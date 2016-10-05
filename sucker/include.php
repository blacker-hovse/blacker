<?
function generate_nametag($pdo, $mole, $small = false) {
	$class = htmlentities($mole->getClass(true), NULL, 'UTF-8');
	$name = htmlentities($mole->name, NULL, 'UTF-8');
	$position = htmlentities($mole->position, NULL, 'UTF-8');
	$location = htmlentities($mole->getLocation(), NULL, 'UTF-8');
	$majors = htmlentities(implode(', ', $mole->getMajors($pdo)), NULL, 'UTF-8');
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

function get_alleys() {
	return array(
		'Cannes',
		'Heaven',
		'Hell',
		'Kremlin',
		'Pub',
		'Swamp',
		'Tunnel',
		'Upper P',
		'Vatican',
		'Fort Knight',
		'Munth',
		'Womb'
	);
}
?>
