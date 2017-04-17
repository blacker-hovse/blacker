<?
include(__DIR__ . '/../lib/class/Mole.class.php');
include(__DIR__ . '/../lib/include.php');
include(__DIR__ . '/../sucker/include.php');

$alleys = get_alleys();

function hovselist_print($moles) {
  echo <<<EOF
      <div>
        <table class="hovselist">
          <thead>
            <th>Name</th>
            <th>Class</th>
            <th>Position</th>
            <th>Location</th>
            <th>Email</th>
            <th>Phone</th>
          </thead>
          <tbody>

EOF;

  foreach ($moles as $mole) {
    $name = htmlentities($mole->name, NULL, 'UTF-8');
    $class = $mole->getClass(false);
    $position = htmlentities($mole->position, NULL, 'UTF-8');
    $location = $mole->getLocation();
    $email = htmlentities($mole->email, NULL, 'UTF-8');
    $phone = htmlentities($mole->phone, NULL, 'UTF-8');

    echo <<<EOF
            <tr>
              <td>$name</td>
              <td>$class</td>
              <td class="col-position">$position</td>
              <td>$location</td>
              <td>$email</td>
              <td>$phone</td>
            </tr>

EOF;
  }

  echo <<<EOF
          </tbody>
        </table>
      </div>

EOF;
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<?
print_head('Hovselist');
?>    <script type="text/javascript" src="/lib/js/jquery.min.js"></script>
    <script type="text/javascript">// <![CDATA[
      function f() {
        var h = $(document).scrollTop();
        var i = $(window).width();
        var j = $(window).height();
        var k = $('.phead').outerHeight();

        $('.phead').each(function() {
          $(this).find('img').css('top', (1 - ($(this).offset().top - h + k) / (j + k)) * (k - i / 3));
          console.log(($(this).offset().top - h + k) / (j + k));
        });
      }

      $(function() {
        f();
        $(document).scroll(f);
      });
    // ]]></script>
  </head>
  <body>
    <div id="main">
      <h1>Hovselist</h1>
<?
$subtitles = array(
  'Better Than Donut',
  'Find Your People',
  'Found the Mole',
  'Listing the Hovse',
  'So Many Moles',
  'Sponsored by NSA',
  'Time to Stalk',
  'Where is Everyone'
);

$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];

echo <<<EOF
      <h2>$subtitle</h2>

EOF;

$pdo = new PDO('sqlite:../sucker/hovselist.db');
$hovse = "('" . implode("', '", $alleys) . "')";

$result = $pdo->prepare(<<<EOF
SELECT `alley`, *
FROM `moles`
WHERE `alley` IN $hovse
ORDER BY `alley`, CAST(`location` AS int), `location`
EOF
  );

$result->execute(array());
$rows = $result->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_GROUP, 'Mole');

foreach ($alleys as $alley) {
  $lower = strtolower(preg_replace(array('/\W+/', '/^_|_$/'), array('_', ''), $alley));

  echo <<<EOF
      <div class="phead">
        <img src="$lower.png" alt="" />
        <h3>$alley</h3>
      </div>

EOF;

  hovselist_print($rows[$alley]);
}

$result = $pdo->prepare(<<<EOF
SELECT `alley`, *
FROM `moles`
WHERE `alley` NOT IN $hovse
  AND `alley` <> 'Social'
ORDER BY `alley`, `location`
EOF
  );

echo <<<EOF
      <div class="phead">
        <img src="off_campus.png" alt="" />
        <h3>Off-Campus</h3>
      </div>

EOF;

$result->execute(array());
hovselist_print($result->fetchAll(PDO::FETCH_CLASS, 'Mole'));

$result = $pdo->prepare(<<<EOF
SELECT `alley`, *
FROM `moles`
WHERE `alley` = 'Social'
ORDER BY `location`
EOF
  );

echo <<<EOF
      <div class="phead">
        <img src="social.png" alt="" />
        <h3>Social Members</h3>
      </div>

EOF;

$result->execute(array());
hovselist_print($result->fetchAll(PDO::FETCH_CLASS, 'Mole'));
?>    </div>
<?
print_footer(
  'Copyright &copy; 2016 Will Yu',
  'A service of Blacker House'
);
?>  </body>
</html>
