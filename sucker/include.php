<?
function generate_nametag($pdo, $mole, $small = false) {
	$class = $mole->getClass(true);
	$name = htmlentities($mole->name, NULL, 'UTF-8');
	$position = htmlentities($mole->position, NULL, 'UTF-8');
	$location = $mole->getLocation();
	$majors = $mole->getMajors($pdo);
	$string = '';

	foreach ($majors as $short => $long) {
		$string .= ', ' . htmlentities(strlen(preg_replace('/[^A-Z]/', '', $short)) < 3 ? $long : $short, NULL, 'UTF-8');
	}

	$string = substr($string, 2);
	$small = $small ? ' nametag-sm' : '';

	echo <<<EOF
				<div class="nametag$small">
					<span class="top">
						<span class="big">$name</span>
						<span>$class</span>
					</span>
					<span class="bottom">
						<span>$string</span>
						<span>$position</span>
					</span>
					<span class="left">
						<span>$location</span>
					</span>
				</div>

EOF;
}
?>
