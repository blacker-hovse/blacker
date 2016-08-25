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

	public function getCohort() {
		$classes = array(
			'Senior',
			'Junior',
			'Smore',
			'Frosh'
		);

		return $classes[$this->cohort - date('Y') - (date('n') > 6)];
	}

	public function getLocation() {
		$location = "$this->location";

		if ($this->alley != 'Social') {
			$location = "$this->alley " . $location;
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
			$majors[$mole['short']] = strlen(preg_replace('/[^A-Z]/', '', $mole['short'])) < 3 ? $mole['long'] : $mole['short'];
		}

		return $majors;
	}
}
?>

