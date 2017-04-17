<?
include(__DIR__ . '/../lib/class/Mole.class.php');
include(__DIR__ . '/../lib/include.php');
include(__DIR__ . '/../sucker/include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<?
print_head('Nametag');
?>  </head>
  <body>
    <div id="main">
      <h1>Nametag</h1>
<?
if (array_key_exists('uid', $_GET)) {
  echo <<<EOF
      <h2>Automagic</h2>
      <div>

EOF;

  $pdo = new PDO('sqlite:../sucker/hovselist.db');
  generate_nametag($pdo, Mole::getMoleByUid($pdo, (int) $_GET['uid']));

  echo <<<EOF
      </div>

EOF;
} else {
  echo <<<EOF
      <h2>Enter Your UID</h2>
      <form action="./" method="get">
        <div class="form-control">
          <label for="uid">UID</label>
          <div class="input-group">
            <input type="text" id="uid" name="uid" />
          </div>
        </div>
        <div class="form-control">
          <div class="input-group">
            <input type="submit" value="Submit" />
          </div>
        </div>
      </form>

EOF;
}
?>    </div>
<?
print_footer(
  'Copyright &copy; 2016 Will Yu',
  'A service of Blacker House'
);
?>  </body>
</html>
