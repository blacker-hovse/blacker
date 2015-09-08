<?
include(__DIR__ . '/../../../lib/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head($title);
?>	</head>
	<body>
<?
print_header();
?>		<div id="main">
			<h1>
<?
echo $heading;
?></h1>
			<div class="text-center qdbs-nav"><a href="./"><b>home</b></a> / <a href="./?p=top"><b>top</b></a> / <a href="./?p=bottom"><b>bottom</b></a> / <a href="./?p=latest"><b>latest</b></a> / <a href="./?p=random"><b>random</b></a><a href="./?p=random1"><b>&gt;0</b></a> / <a href="./?p=browse"><b>browse</b></a> / <a href="./?p=search"><b>search</b></a> / <a href="./?p=add"><b>add</b></a> / <a href="./admin/"><b>admin</b></a></div>
