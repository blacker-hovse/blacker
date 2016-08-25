<?
class Mole {
	public $uid;
	public $name;
	public $legal;
	public $class;
	public $cohort;
	public $position;
	public $email;
	public $phone;
	public $alley;
	public $location;
	public $terms;

	public function getClass($social) {
		$classes = array(
			'Senior',
			'Junior',
			'Smore',
			'Frosh'
		);

		$class = $this->class - date('Y') - (date('n') > 6);
		$class = $class < 0 ? 'Supersenior' : $classes[$class];

		if ($social and $this->alley == 'Social') {
			$class = 'Social ' . $class;
		}

		return $class;
	}

	public function getLocation() {
		$location = htmlentities("$this->location", NULL, 'UTF-8');

		if ($this->alley != 'Social') {
			$location = htmlentities("$this->alley ", NULL, 'UTF-8') . $location;
		}

		return $location;
	}

	public function getMajors($pdo) {
		$subresult = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
	INNER JOIN `mole_majors`
		ON `major` = `short`
WHERE `mole` = :uid
EOF
			);

		$subresult->execute(array(
			':uid' => $this->uid
		));

		$majors = array();

		while ($mole = $subresult->fetch(PDO::FETCH_ASSOC)) {
			$majors[$mole['short']] = $mole['long'];
		}

		return $majors;
	}
}
?>

