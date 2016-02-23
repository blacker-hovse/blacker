<?
function generate_nametag($pdo, $row, $small = false) {
	$classes = array(
		'Senior',
		'Junior',
		'Smore',
		'Frosh'
	);

	$class = $row['class'] - date('Y') - (date('n') > 6);
	$class = $class < 0 ? 'Supersenior' : $classes[$class];

	$subresult = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
	INNER JOIN `mole_majors`
		ON `major` = `short`
WHERE `mole` = :uid
EOF
		);

	$subresult->execute(array(
		':uid' => $row['uid']
	));

	$name = htmlentities($row['name'], NULL, 'UTF-8');
	$position = htmlentities($row['position'], NULL, 'UTF-8');
	$location = htmlentities("$row[location]", NULL, 'UTF-8');

	if ($row['alley'] == 'Social') {
		$class = 'Social ' . $class;
	} else {
		$location = htmlentities("$row[alley] ", NULL, 'UTF-8') . $location;
	}

	$majors = '';

	while ($row = $subresult->fetch(PDO::FETCH_ASSOC)) {
		$majors .= ', ' . htmlentities($row[strlen(preg_replace('/[^A-Z]/', '', $row['short'])) < 3 ? 'long' : 'short'], NULL, 'UTF-8');
	}

	$majors = substr($majors, 2);
	$small = $small ? ' nametag-sm' : '';

	echo <<<EOF
				<div class="nametag$small">
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

EOF;
}
?>
