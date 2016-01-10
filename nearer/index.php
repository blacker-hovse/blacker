<?
// HACK
$_SERVER['PHP_AUTH_USER'] = 'jrmole';

include(__DIR__ . '/../lib/include.php');

$create = !file_exists('nearer.db');
$pdo = new PDO('sqlite:nearer.db');

if ($create) {
	$pdo->exec(<<<EOF
CREATE TABLE `history` (
	`user` varchar(64) NOT NULL,
	`v` varchar(16) NOT NULL,
	`created` datetime NOT NULL
)
EOF
		);
}

if (array_key_exists('action', $_GET)) {

}

if (array_key_exists('url', $_POST)) {
	if (preg_match('/[\w-]{11}/', $_POST['url'], $matches)) {
		$result = $pdo->prepare(<<<EOF
INSERT INTO `history` (
	`user`,
	`v`,
	`created`
)
VALUES (
	:user,
	:v,
	DATETIME('now')
)
EOF
			);

		$result->execute(array(
			':user' => $_SERVER['PHP_AUTH_USER'],
			':v' => $matches[0]
		));

		$success = 'Successfully added video to queue.';
	} else {
		$error = 'Invalid URL or video ID.';
	}
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Nearer');
?>	</head>
	<body>
		<div id="main">
			<h1>Nearer</h1>
<?
$subtitles = array(
	'Beats Ricketts Music',
	"Don't Play the Ride",
	'Play Loud, Play Proud',
	'The Day the Music Died'
);

$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];

echo <<<EOF
			<h2>$subtitle</h2>

EOF;
?>			<form action="./" method="post">
				<div class="form-control">
					<label for="url">YouTube URL</label>
					<div class="input-group">
						<input type="text" id="url" name="url" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
			<h2>Recently Added</h2>
			<p class="text-center">
				<a class="btn btn-lg" href="?action=play">&#9654;</a>
				<a class="btn btn-lg" href="?action=skip">&#9197;</a>
				<a class="btn btn-lg" href="?action=stop">&#9724;</a>
			</p>
<?
$result = $pdo->prepare(<<<EOF
SELECT *
FROM `history`
ORDER BY `created` DESC
LIMIT 10
EOF
	);

$result->execute();

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$url = 'http://www.youtube.com/watch?v=' . $row['v'];
	$data = json_decode(file_get_contents("http://www.youtube.com/oembed?url=$url&format=json"));
	$title = htmlentities($data->title, NULL, 'UTF-8');
	$author_name = htmlentities($data->author_name, NULL, 'UTF-8');
	$author_url = htmlentities($data->author_url, NULL, 'UTF-8');
	$thumbnail = htmlentities($data->thumbnail_url, NULL, 'UTF-8');

	echo <<<EOF
			<div class="media">
				<div class="pull-left">
					<img src="$thumbnail" />
				</div>
				<h4>
					<a href="$url">$title</a>
				</h4>
				<p>Uploaded by <a href="$author_url">$author_name</a></p>
				<p>Added by $row[user] on $row[created]</p>
			</div>

EOF;
}
?>		</div>
<?
print_footer(
	'Copyright &copy; 2016 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
