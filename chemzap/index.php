<?
function chemzap_normalize($str) {
  return htmlentities(strtolower(preg_replace('/^(\w+).*?(\w+)\s*$/', '$1$2', $str)), NULL, 'UTF-8', false);
}

include(__DIR__ . '/../lib/class/Mole.class.php');
include(__DIR__ . '/../lib/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<?
print_head('ChemZap');
?>  </head>
  <body>
    <div id="main">
      <h1>ChemZap</h1>
      <h2>Automagic</h2>
      <div>
<?
$pdo = new PDO('sqlite:../sucker/hovselist.db');

$statement = <<<EOF
SELECT *
FROM `moles`
WHERE `cohort` > 2019
AND `uid` <> 0
EOF;

$result = $pdo->prepare($statement);
$result->execute();
$mugshots = array();
$donut = str_replace(array("\n"), '', file_get_contents('chemzap.txt'));

preg_replace_callback('/SRC=\'([^\']*)\'><br>([^<]+)/', function($matches) {
  global $mugshots;

  $mugshots[chemzap_normalize($matches[2])] = 'http://donut.caltech.edu/directory/' . $matches[1];
}, $donut);

while ($mole = $result->fetchObject('Mole')) {
  $name = htmlentities($mole->name, NULL, 'UTF-8');
  $normalized_name = chemzap_normalize($name);
  $photo = file_exists($normalized_name . '.jpg') ? $normalized_name . '.jpg' : @$mugshots[$normalized_name];

  echo <<<EOF
        <div id="$normalized_name" class="chemzap">
          <span class="chemzap-name">$name</span>
          <img src="$photo" class="chemzap-photo" />
        </div>

EOF;
}
?>      </div>
    </div>
<?
print_footer(
  'Copyright &copy; 2016 Will Yu',
  'A service of Blacker House'
);
?>  </body>
</html>


