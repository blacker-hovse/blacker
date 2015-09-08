<?
include(__DIR__ . '/../../../lib/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head($title);
?>		<style type="text/css">
			.quote-block h2 {
				display: none;
			}
		</style>
	</head>
	<body>
<?
print_header();
?>		<div id="main">
			<h1>
<?
echo $heading;
?></h1>
			<div class="text-center"><?
echo $logged;
?></div>
			<h2>QdbS Admin</h2>
