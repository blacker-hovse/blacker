<?
class Mole {
  public $uid;
  public $name;
  public $legal;
  public $gender;
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
  private $genderBak;
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
      'gender' => 'Gender',
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

  public static function killMoleByUid($pdo, $uid) {
    $parameters = array(
      ':uid' => $uid
    );

    $result = $pdo->prepare(<<<EOF
DELETE FROM `moles`
WHERE `uid` = :uid
EOF
      );

    if (!$result) {
      return $pdo->errorInfo()[2];
    }

    if (!$result->execute($parameters)) {
      return $result->errorInfo()[2];
    }

    $result = $pdo->prepare(<<<EOF
DELETE FROM `mole_majors`
WHERE `mole` = :uid
EOF
      );

    if (!$result) {
      return $pdo->errorInfo()[2];
    }

    if (!$result->execute($parameters)) {
      return $result->errorInfo()[2];
    }
  }

  public static function yearToCohort($year) {
    $classes = self::getClasses();
    return @$classes[$year - date('Y') - (date('n') >= 7)];
  }

  public function __construct() {
    $fields = array_slice(self::getFields(), 1, -1);

    foreach ($fields as $field => $label) {
      $bak = $field . 'Bak';
      $this->$bak = $this->$field;
    }
  }

  public function getClass($social) {
    $classes = self::getClasses();
    $class = $this->class - date('Y') - (date('n') >= 7);
    $class = $class < 0 ? 'Supersenior' : @$classes[$class];

    if ($social and $this->alley == 'Social') {
      $class = 'Social ' . $class;
    }

    return $class;
  }

  public function getCohort() {
    return self::yearToCohort($this->cohort);
  }

  public function getLocation() {
    $location = $this->location;

    if ($this->alley != 'Social') {
      $location = "$this->alley " . $location;
    }

    return $location;
  }

  public function getMajors($pdo) {
    $result = $pdo->prepare(<<<EOF
SELECT `majors`.*
FROM `majors`
INNER JOIN `mole_majors` ON `major` = `short`
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
    $this->normalize();

    if (self::getMoleByUid($pdo, $this->uid)) {
      return 'UID ' . (int) $this->uid . ' already exists.';
    }

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

    if (!$result->execute($parameters)) {
      return $result->errorInfo()[2];
    }
  }

  public function normalize() {
    $this->uid = (float) $this->uid;
    $this->class = (int) $this->class;
    $this->cohort = (int) $this->cohort;
    $this->terms = (float) $this->terms;
    $this->classBak = (int) $this->classBak;
    $this->cohortBak = (int) $this->cohortBak;
    $this->termsBak = (float) $this->termsBak;
  }

  public function setMajors($pdo, $majors) {
    $parameters = array(
      ':uid' => $this->uid
    );

    $result = $pdo->prepare(<<<EOF
DELETE FROM `mole_majors`
WHERE `mole` = :uid
EOF
      );

    if (!$result) {
      return $pdo->errorInfo()[2];
    }

    if (!$result->execute(array(
      ':uid' => $this->uid
    ))) {
      return $result->errorInfo()[2];
    }

    $vals = array();

    foreach ($majors as $i => $major) {
      $parameters[':major' . $i] = $major;
      $vals[] = "(:uid, :major$i)";
    }

    $vals = implode(',
  ', $vals);

    if (!$vals) {
      return;
    }

    $result = $pdo->prepare(<<<EOF
INSERT INTO `mole_majors` (
  `mole`,
  `major`
)
VALUES $vals
EOF
      );

    if (!$result) {
      return $pdo->errorInfo()[2];
    }

    if (!$result->execute($parameters)) {
      return $result->errorInfo()[2];
    }
  }

  public function update($pdo) {
    $this->normalize();
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
      return;
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

    if (!$result->execute($parameters)) {
      return $result->errorInfo()[2];
    }
  }
}
?>

