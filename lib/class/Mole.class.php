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
	private $nameBak;
	private $legalBak;
	private $classBak;
	private $cohortBak;
	private $positionBak;
	private $emailBak;
	private $phoneBak;
	private $alleyBak;
	private $locationBak;
	private $termsBak;

	public static function getClasses() {
		return array(
			'Senior',
			'Junior',
			'Smore',
			'Frosh'
		);
	}

	public static function getFields() {
		return array(
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
	}

	public function __construct() {
		$fields = self::getFields();

		foreach ($fields as $field => $label) {
			if ($field != 'uid') {
				$bak = $field . 'Bak';
				$this->$bak = $this->$field;
			}
		}
	}

	public function getClass($social) {
		$classes = self::getClasses();
		$class = $this->class - date('Y') - (date('n') > 6);
		$class = $class < 0 ? 'Supersenior' : @$classes[$class];

		if ($social and $this->alley == 'Social') {
			$class = 'Social ' . $class;
		}

		return $class;
	}

	public function getCohort() {
		$classes = self::getClasses();
		return @$classes[$this->cohort - date('Y') - (date('n') >= 7)];
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

