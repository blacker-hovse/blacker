<?
include(__DIR__ . '/../lib/include.php');

function blink_long($short) {
	global $pdo;

	$result = $pdo->prepare('SELECT `long` FROM `blink` WHERE `short` = :short');

	$result->execute(array(
		':short' => $short
	));

	return $result->fetchColumn();
}

function blink_short($long) {
	global $alphabet;
	global $pdo;

	$result = $pdo->prepare('SELECT `short` FROM `blink` WHERE `long` = :long');

	$result->execute(array(
		':long' => $long
	));

	$short = $result->fetchColumn();

	if (!$short) {
		for ($i = (int) log((int) $pdo->query('SELECT COUNT(*) FROM `blink`')->fetchColumn() + 1, strlen($alphabet)) + 1; $pdo->query("SELECT COUNT(*) FROM `blink` WHERE LENGTH(`short`) > $i")->fetchColumn(); $i++);
		$result = $pdo->prepare('SELECT COUNT(*) FROM `blink` WHERE `short` = :short');

		do {
			$short = substr(str_shuffle($alphabet), 0, $i);

			$result->execute(array(
				':short' => $short
			));
		} while ($result->fetchColumn());

		$result = $pdo->prepare('INSERT INTO `blink` (`long`, `short`) VALUES (:long, :short)');

		$result->execute(array(
			':long' => $long,
			':short' => $short
		));
	}

	return $short;
}

function blink_view($short) {
	global $pdo;

	$result = $pdo->prepare('UPDATE `blink` SET `freq` = `freq` + 1 WHERE `short` = :short');

	$result->execute(array(
		':short' => $short
	));
}

$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
$db = 'blink.db';
$create = !file_exists($db);
$short = false;
$pdo = new PDO('sqlite:' . $db);

if ($create) {
	$pdo->exec(<<<EOF
CREATE TABLE `blink` (
	`long` varchar(255) UNIQUE NOT NULL,
	`short` varchar(16) PRIMARY KEY NOT NULL,
	`freq` unsigned int(10) NOT NULL DEFAULT '0'
)
EOF
		);
}

if (@$_REQUEST['u']) {
	if (@$_GET['u'] and $url = blink_long($_GET['u'])) {
		blink_view($_GET['u']);
		header('Location: ' . $url);
		die();
	}

	if (@$_POST['u'] and $url = filter_input(INPUT_POST, 'u', FILTER_SANITIZE_URL)) {
		if (!preg_match('/^\w+:\/\//', $url)) {
			$url = 'http://' . $url;
		}

		$short = blink_short($url);
	}
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Blink');
?>	</head>
	<body>
	    <div id="main">
			<h1>Blink</h1>
<?
$subtitles = array(
	'Baby Shoes',
	'Beats TinyURL',
	'Gracious Links',
	'Less is More',
	'Malcolm Gladwell',
	'Oven Aye',
	'Short and Sweet',
	'Shortens Links',
	'Size Matters',
	'Suck it TinyURL',
	"Vive l'Anarchie",
	'Welcome FBI'
);

$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];

echo <<<EOF
			<h2>$subtitle</h2>

EOF;

if (isset($_GET['list'])) {
	echo <<<EOF
			<div>
				<p>All URLs are listed below.</p>
				<table>
					<tr>
						<th>Long URL</th>
						<th>Short URL</th>
						<th>Hits</th>
					</tr>

EOF;

	$result = $pdo->query('SELECT * FROM `blink` ORDER BY `freq` DESC');

	while ($row = $result->fetch()) {
		$url = htmlentities($row['long'], NULL, 'UTF-8');

		echo <<<EOF
					<tr>
						<td>$url</td>
						<td>$row[short]</td>
						<td>$row[freq]</td>
					</tr>

EOF;
	}

	echo <<<EOF
				</table>
			</div>

EOF;
} else {
	$options = '';

	if ($short) {
		$url = 'http';

		if (@$_SERVER['HTTPS']) {
			$url .= 's';
		}

		$url .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		if ($i = strpos($url, '?')) {
			$url = substr($url, 0, $i);
		}

		$options = " value=\"$url?u=$short\" readonly=\"readonly\"";
	}

	echo <<<EOF
			<form action="./" method="post">
				<input type="text" name="u"$options />
			</form>

EOF;
}
?>		</div>
<?
print_footer(
	'Copyright &copy; 2015 Keegan Ryan and Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
