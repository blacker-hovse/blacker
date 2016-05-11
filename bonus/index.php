<?
include(__DIR__ . '/../lib/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Bonus');
?>	</head>
	<body>
		<div id="main">
			<h1>Bonus</h1>
			<h2>You Win</h2>
			<audio autoplay="autoplay" style="visibility: hidden;">
				<source src="/lib/ride.mp3" type="audio/mpeg" />
			</audio>
		</div>
<?
print_footer(
	'Copyright &copy; 2016 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
