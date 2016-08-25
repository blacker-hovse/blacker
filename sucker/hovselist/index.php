<?
include(__DIR__ . '/../../lib/class/Mole.class.php');
include(__DIR__ . '/../../lib/include.php');
include(__DIR__ . '/../include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Hovselist');
?>	</head>
	<body>
	    <div id="main">
			<h1>Hovselist</h1>
			<h2>Feel the Power</h2>
			<p></p>
			<table class="hovselist">
				<tr>
					<th><?
$cols = array(
	'uid' => 'UID',
	'name' => 'Name',
	'legal' => 'Legal',
	'class' => 'Class',
	'cohort' => 'Cohort',
	'position' => 'Position',
	'email' => 'Email',
	'phone' => 'Phone',
	'alley' => 'Alley',
	'location' => 'Location',
	'terms' => 'Terms'
);

echo implode(<<<EOF
</th>
					<th>
EOF
	, $cols);
?></th>
					<th>Major</th>
				</tr>
<?
$pdo = new PDO('sqlite:../hovselist.db');

$statement = <<<EOF
SELECT `uid`,
	`name`,
	`legal`,
	`class`,
	`cohort`,
	`position`,
	`email`,
	`phone`,
	`alley`,
	`location`,
	`terms`
FROM `moles`
WHERE `alley` <> 'Social'
EOF;

$result = $pdo->prepare($statement);
$result->execute();

while ($mole = $result->fetchObject('Mole')) {
	echo <<<EOF
				<tr id="u$mole->uid">

EOF;

	$majors = $mole->getMajors($pdo);

	foreach ($cols as $col => $label) {
		$val = $col == 'cohort' ? $mole->getCohort() : $mole->$col;

		echo <<<EOF
					<td class="col-$col">$val</td>

EOF;
	}

	echo <<<EOF
					<td class="col-major">

EOF;

	foreach ($majors as $short => $long) {
		echo <<<EOF
						<span class="maj-$short">$long</span>

EOF;
	}

	echo <<<EOF
					</td>
				</tr>

EOF;
}
?>			</table>
	    </div>
<?
print_footer(
	'Copyright &copy; 2016 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
