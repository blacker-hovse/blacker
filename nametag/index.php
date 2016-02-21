<?
include(__DIR__ . '/../lib/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Nametag');
?>		<link href="//fonts.googleapis.com/css?family=Squada+One" rel="stylesheet" type="text/css" />
		<link href="/lib/css/nametag.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="main">
			<h1>Nametag</h1>
<?
$classes = array(
	'Senior',
	'Junior',
	'Smore',
	'Frosh'
);

if (array_key_exists('uid', $_GET)) {
	$parameters = array(
		':uid' => (int) $_GET['uid']
	);

	$pdo = new PDO('sqlite:../sucker/hovselist.db');
	$result = $pdo->prepare(<<<EOF
SELECT *
FROM `moles`
WHERE `uid` = :uid
EOF
		);

	$result->execute($parameters);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$class = $row['class'] - date('Y') - (date('n') > 6);
	$class = $class < 0 ? 'Supersenior' : $classes[$class];

	if (!$row or strtolower($row['alley']) == 'social') {
		echo <<<EOF
			<h2>User Not Found</h2>

EOF;
	} else {
		$result = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
	INNER JOIN `mole_majors`
		ON `major` = `short`
WHERE `mole` = :uid
EOF
			);

		$result->execute($parameters);
		$name = htmlentities($row['name'], NULL, 'UTF-8');
		$position = htmlentities($row['position'], NULL, 'UTF-8');
		$location = htmlentities("$row[alley] $row[location]", NULL, 'UTF-8');
		$majors = '';

		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$majors .= ', ' . htmlentities($row[strlen(preg_replace('/[^A-Z]/', '', $row['short'])) < 3 ? 'long' : 'short'], NULL, 'UTF-8');
		}

		$majors = substr($majors, 2);

		echo <<<EOF
			<h2>Automagic</h2>
			<div>
				<div class="nametag">
					<span class="top">
						<span class="big">$name</span>
						<span>$class</span>
					</span>
					<span class="bottom">
						<span>$majors</span>
						<span>$position</span>
					</span>
					<span class="left">
						<span>$location</span>
					</span>
				</div>
			</div>

EOF;
	}
} else {
	echo <<<EOF
			<h2>Enter Your UID</h2>
			<form action="./" method="get">
				<div class="form-control">
					<label for="uid">UID</label>
					<div class="input-group">
						<input type="text" id="uid" name="uid" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>

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
