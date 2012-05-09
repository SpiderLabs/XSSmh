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
| <a href="xss.php">Reflected Cross-Site Scripting</a> || <a href="pxss.php">Persistent Cross-Site Scripting</a> || <a href="challenges.htm">Challenges</a> | 
<hr width="40%">
<hr width="60%">
<hr width="40%">
<br>
<form name='inject_form'>
	<table><tr><td>Injection String:</td><td><textarea name='inject_string'></textarea></td></tr>
	<tr><td>Injection Location:</td><td>
		<select name="location">
			<option value="body">Body</option>
			<option value="attribute_single">Attribute value (wrapped in single quotes)</option>
			<option value="attribute_double">Attribute value (wrapped in double quotes)</option>
			<option value="attribute_noquotes">Attribute value (not wrapped in quotes)</option>
		</select></td></tr>
	<tr><td><b>Input Sanitization:</b></td></tr>
		<tr><td>Remove Quotes?</td><td><input type='checkbox' name="quotes_remove"></td></tr>
		<tr><td>Remove Spaces?</td><td><input type="checkbox" name="spaces_remove"></td></tr>
		<tr><td>Remove Slashes?</td><td><input type="checkbox" name="slashes_remove"></td></tr>
		<tr><td>Remove Angle Brackets &lt; &gt;?</td><td><input type="checkbox" name="angle_remove" <?php echo (isset($_REQUEST['angle_remove']) ? 'checked' : ''); ?>></td></tr>
	</table>
	<input type="submit" name="submit" value="Inject!">
</form>

<?php
if(isset($_REQUEST['submit'])){
	$base_output = 'Foo! <input type="text" value="bar!">';
	
	//sanitization section
	if(isset($_REQUEST['quotes_remove']) and $_REQUEST['quotes_remove'] == 'on') $_REQUEST['inject_string'] = str_replace("'", "", $_REQUEST['inject_string']);
	if(isset($_REQUEST['spaces_remove']) and $_REQUEST['spaces_remove'] == 'on') $_REQUEST['inject_string'] = str_replace(' ', '', $_REQUEST['inject_string']);
	if(isset($_REQUEST['slashes_remove']) and $_REQUEST['slashes_remove'] == 'on') $_REQUEST['inject_string'] = str_replace('/', '', $_REQUEST['inject_string']);
	if(isset($_REQUEST['angle_remove']) and $_REQUEST['angle_remove'] == 'on'){
		$_REQUEST['inject_string'] = str_replace('<', '', $_REQUEST['inject_string']);
		$_REQUEST['inject_string'] = str_replace('>', '', $_REQUEST['inject_string']);
	}
	
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
	}
	
	echo $output;
	
}

?>
</body>
</html>