<?php
/*
XSSmh - A configurable Cross-Site Scripting testbed
Daniel "unicornFurnace" Crowley
Copyright (C) 2012 Trustwave Holdings, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
?>
<html>
<head>
<title>XSSmh - Cross-Site Scripting</title>
</head>
<body>
<center><h1>XSSmh - Cross-Site Scripting</h1></center><br>
<center>| <a href="xss.php">Cross-Site Scripting</a> || <a href="challenges.htm">Challenges</a> | </center>
<hr width="40%">
<hr width="60%">
<hr width="40%">
<br>
<form name='inject_form'>
	<table><tr><td>Injection String:</td><td><textarea name='inject_string'><?php echo (isset($_REQUEST['inject_string']) ? htmlentities($_REQUEST['inject_string']) : '' ); ?></textarea></td></tr>
	<tr><td>Injection Location:</td><td>
		<select name="location">
			<option value="body">Body</option>
			<option value="attribute_single" <?php if(isset($_REQUEST["location"]) and $_REQUEST["location"]=="attribute_single") echo "selected"; ?>>Attribute value (wrapped in single quotes)</option>
			<option value="attribute_double" <?php if(isset($_REQUEST["location"]) and $_REQUEST["location"]=="attribute_double") echo "selected"; ?>>Attribute value (wrapped in double quotes)</option>
			<option value="attribute_noquotes" <?php if(isset($_REQUEST["location"]) and $_REQUEST["location"]=="attribute_noquotes") echo "selected"; ?>>Attribute value (not wrapped in quotes)</option>
			<option value="image_src" <?php if(isset($_REQUEST["location"]) and $_REQUEST["location"]=="image_src") echo "selected"; ?>>Image URL</option>
			<option value="javascript" <?php if(isset($_REQUEST["location"]) and $_REQUEST["location"]=="javascript") echo "selected"; ?>>JavaScript</option>
		</select></td></tr>
		<tr><td>Custom HTML (*INJECT* specifies injection point):</td><td><textarea name="custom_inject"><?php echo (isset($_REQUEST['custom_inject']) ? htmlentities($_REQUEST['custom_inject']) : '' ); ?></textarea></td></tr>
	<tr><td>Persistent?</td><td><input type='checkbox' name='persistent' <?php echo (isset($_REQUEST['persistent']) ? 'checked' : ''); ?>>
	<tr><td><b>Input Sanitization:</b></td></tr>
	<tr><td>Blacklist Level:</td><td><select name="blacklist_level">
		<option value="none">No blacklisting</option>
		<option value="reject_low" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="reject_low") echo "selected"; ?>>Reject (Low)</option>
		<option value="reject_high" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="reject_high") echo "selected"; ?>>Reject (High)</option>
		<option value="escape" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="escape") echo "selected"; ?>>Escape</option>
		<option value="low" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="low") echo "selected"; ?>>Remove (Low)</option>
		<option value="medium" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="medium") echo "selected"; ?>>Remove (Medium)</option>
		<option value="high" <?php if(isset($_REQUEST["blacklist_level"]) and $_REQUEST["blacklist_level"]=="high") echo "selected"; ?>>Remove (High)</option>
	</select></td></tr>
	<tr><td>Blacklist Keywords (comma separated):</td><td><textarea name="blacklist_keywords"><?php if(isset($_REQUEST["blacklist_keywords"])) echo $_REQUEST["blacklist_keywords"]; ?></textarea></td></tr>
	</table>
	<input type="submit" name="submit" value="Inject!">
</form>

<?php
if(isset($_REQUEST['submit'])){
	$base_output = 'Foo! <img src="baz.jpg"><input type="text" value="bar!"><script>a="javascript";</script>';
	
	//sanitization section
	if(isset($_REQUEST['blacklist_keywords'])){
		$blacklist = explode(',' , $_REQUEST['blacklist_keywords']);
	}
	
	if(isset($_REQUEST['blacklist_level'])){
		switch($_REQUEST['blacklist_level']){
			//We process blacklists differently at each level. At the lowest, each keyword is removed case-sensitively.
			//At medium blacklisting, checks are done case-insensitively.
			//At the highest level, checks are done case-insensitively and repeatedly.
			
			case 'reject_low':
				foreach($blacklist as $keyword){
					if(strstr($_REQUEST['inject_string'], $keyword)!='') {
						die("\nBlacklist was triggered!");
					}
				}
				break;
			case 'reject_high':
				foreach($blacklist as $keyword){
					if(strstr(strtolower($_REQUEST['inject_string']), strtolower($keyword))!='') {
						die("\nBlacklist was triggered!");
					}
				}
				break;
			case 'escape':
				foreach($blacklist as $keyword){
					$_REQUEST['inject_string'] = str_replace($keyword, htmlentities($keyword), $_REQUEST['inject_string']);
				}
				break;
			case 'low':
				foreach($blacklist as $keyword){
					$_REQUEST['inject_string'] = str_replace($keyword, '', $_REQUEST['inject_string']);
				}
				break;
			case 'medium':
				foreach($blacklist as $keyword){
					$_REQUEST['inject_string'] = str_ireplace($keyword, '', $_REQUEST['inject_string']);
				}
				break;
			case 'high':
				do{
					$keyword_found = 0;
					foreach($blacklist as $keyword){
						$_REQUEST['inject_string'] = str_ireplace($keyword, '', $_REQUEST['inject_string'], $count);
						$keyword_found += $count;
					}	
				}while ($keyword_found);
				break;
			
		}
	}
	
	if (isset($_REQUEST['custom_inject']) and $_REQUEST['custom_inject']!=''){
		$output = str_replace('*INJECT*', $_REQUEST['inject_string'], $_REQUEST['custom_inject']);
	}else{
		switch ($_REQUEST['location']){
			case 'body':
				$output = str_replace('Foo!', $_REQUEST['inject_string'], $base_output);
				break;
			case 'attribute_single':
				$output = str_replace('"bar!"', '\''.$_REQUEST['inject_string'].'\'', $base_output);
				break;
			case 'attribute_double':
				$output = str_replace('bar!', $_REQUEST['inject_string'], $base_output);
				break;
			case 'attribute_noquotes':
				$output = str_replace('"bar!"', $_REQUEST['inject_string'], $base_output);
				break;
			case 'image_src':
				$output = str_replace('baz.jpg', $_REQUEST['inject_string'], $base_output);
				break;
			case 'javascript':
				$output = str_replace('javascript', $_REQUEST['inject_string'], $base_output);
				break;
		}
	}
	
	if(isset($_REQUEST['persistent'])){
	
		$fhandle = fopen('pxss.html','w') or die('Whoops! Can\'t write to our PXSS file. Did you run setup.sh?');
		fwrite($fhandle, $output);
		fclose($fhandle);
	
		echo "<a href='pxss.html'>See the output</a>";
		
	}else{
		
		echo $output;
	
	}
	
}

?>
</body>
</html>
