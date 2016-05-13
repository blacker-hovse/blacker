<?
$config = array();
$config['data_dir'] = '/data/important/gdbg';
$config['web_dir'] = '/gallery2';

include(__DIR__ . '/../lib/include.php');

$data = $config['data_dir'] . '/' . @$_GET['p'];
$web = $config['web_dir'] . '/' . @$_GET['p'];

if (is_dir($data)) {
	if (substr($_GET['p'], -1) != '/') {
		header("Location: $web/", 301);
	}

	echo <<<EOF
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

EOF;

	print_head('Gallery');

	echo <<<EOF
	</head>
	<body>
	    <div id="main">
			<h1>Gallery</h1>
			<h2>Incriminating evidence</h2>

EOF;

	$dh = opendir($data);

	while ($file = readdir($dh)) {
		if (in_array(strtolower(substr($file, -4)), array('.gif', '.jpg', '.png'))) {
			echo <<<EOF
			<img src="$web$file" />

EOF;
		}
	}
} elseif (file_exists($data)) {
	readfile($data);
}
?>
