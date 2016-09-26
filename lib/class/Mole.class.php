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

	public static function getAllMajors($pdo) {
		$result = $pdo->prepare(<<<EOF
SELECT `short`, `long`
FROM `majors`
EOF
			);

		$result->execute();
		return $result->fetchAll(PDO::FETCH_KEY_PAIR);
	}

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
			'terms' => 'Terms',
			'major' => 'Major'
		);
	}

	public static function getMoleByUid($pdo, $uid) {
		$result = $pdo->prepare(<<<EOF
SELECT *
FROM `moles`
WHERE `uid` = :uid
EOF
			);

		$result->execute(array(
			':uid' => $uid
		));

		return $result->fetchObject(__CLASS__);
	}

	public function __construct() {
		$fields = array_slice(self::getFields(), 1);

		foreach ($fields as $field => $label) {
			$bak = $field . 'Bak';
			$this->$bak = $this->$field;
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
		$result = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
	INNER JOIN `mole_majors`
		ON `major` = `short`
WHERE `mole` = :uid
EOF
			);

		$result->execute(array(
			':uid' => $this->uid
		));

		$majors = array();

		while ($mole = $result->fetch(PDO::FETCH_ASSOC)) {
			$majors[$mole['short']] = strlen(preg_replace('/[^A-Z]/', '', $mole['short'])) < 3 ? $mole['long'] : $mole['short'];
		}

		return $majors;
	}

	public function insert($pdo) {
		$fields = array_keys(array_slice(self::getFields(), 0, -1));

		foreach ($fields as $field) {
			$parameters[':' . $field] = $this->$field;
		}

		$cols = implode(',
	', preg_replace('/^.*$/', '`$0`', $fields));

		$vals = implode(',
	', preg_replace('/^/', ':', $fields));

		$result = $pdo->prepare(<<<EOF
INSERT INTO `moles` (
	$cols
)
VALUES (
	$vals
)
EOF
			);

		if (!$result) {
			return $pdo->errorInfo()[2];
		}

		$result->execute($parameters);
		return $result->errorInfo()[2];
	}

	public function update($pdo) {
		$fields = array_slice(self::getFields(), 1, -1);
		$settings = array();

		$parameters = array(
			':uid' => $this->uid
		);

		foreach ($fields as $field => $label) {
			$bak = $field . 'Bak';

			if ($this->$field != $this->$bak) {
				$settings[] = "`$field` = :$field";
				$parameters[':' . $field] = $this->$field;
			}
		}

		$settings = implode(',
	', $settings);

		if (!$settings) {
			return 'No changes were made.';
		}

		$result = $pdo->prepare(<<<EOF
UPDATE `moles`
SET $settings
WHERE `uid` = :uid
EOF
			);

		if (!$result) {
			return $pdo->errorInfo()[2];
		}

		$result->execute($parameters);
		return $result->errorInfo()[2];
	}
}
?>

