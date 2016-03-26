<?
function generate_class($mole, $social) {
	$classes = array(
		'Senior',
		'Junior',
		'Smore',
		'Frosh'
	);

	$class = $mole['class'] - date('Y') - (date('n') > 6);
	$class = $class < 0 ? 'Supersenior' : $classes[$class];

	if ($social and $mole['alley'] == 'Social') {
		$class = 'Social ' . $class;
	}

	return $class;
}

function generate_location($mole) {
	$location = htmlentities("$mole[location]", NULL, 'UTF-8');

	if ($mole['alley'] != 'Social') {
		$location = htmlentities("$mole[alley] ", NULL, 'UTF-8') . $location;
	}

	return $location;
}

function generate_nametag($pdo, $mole, $small = false) {
	$class = generate_class($mole, true);

	$subresult = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
	INNER JOIN `mole_majors`
		ON `major` = `short`
WHERE `mole` = :uid
EOF
		);

	$subresult->execute(array(
		':uid' => $mole['uid']
	));

	$name = htmlentities($mole['name'], NULL, 'UTF-8');
	$position = htmlentities($mole['position'], NULL, 'UTF-8');
	$location = generate_location($mole);
	$majors = '';

	while ($mole = $subresult->fetch(PDO::FETCH_ASSOC)) {
		$majors .= ', ' . htmlentities($mole[strlen(preg_replace('/[^A-Z]/', '', $mole['short'])) < 3 ? 'long' : 'short'], NULL, 'UTF-8');
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
