<?
include(__DIR__ . '/../../lib/class/Mole.class.php');
include(__DIR__ . '/../../lib/include.php');
include(__DIR__ . '/../include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<?
print_head('Nametag');
?>  </head>
  <body>
    <div id="main">
      <h1>Nametag</h1>
      <h2>Automagic</h2>
      <div>
<?
$pdo = new PDO('sqlite:../hovselist.db');

$statement = <<<EOF
SELECT *
FROM `moles`
WHERE `alley` <> 'Social'
EOF;

$result = $pdo->prepare($statement);
$result->execute();

while ($row = $result->fetchObject('Mole')) {
  generate_nametag($pdo, $row);
}

$result = $pdo->prepare($statement);
$result->execute();

while ($row = $result->fetchObject('Mole')) {
  generate_nametag($pdo, $row, true);
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

