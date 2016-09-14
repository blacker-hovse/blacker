<?
include(__DIR__ . '/../../lib/class/Mole.class.php');
include(__DIR__ . '/../../lib/include.php');
include(__DIR__ . '/../include.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Hovselist');
?>		<script type="text/javascript" src="/lib/js/jquery.min.js"></script>
		<script type="text/javascript" src="/lib/js/selectize.min.js"></script>
		<script type="text/javascript">// <![CDATA[
			var classes = [
				'<?
echo implode(<<<EOF
',
				'
EOF
	, Mole::getClasses());
?>'
			];

			var year = new Date();

			year = year.getFullYear() + (year.getMonth() >= 6);

			$(function() {
				$('.hovselist').on('click', '.edit', function() {
					$(this).parent().siblings().each(function() {
						if (!$(this).hasClass('col-uid')) {
							var e = '';

							if ($(this).hasClass('col-cohort')) {
								e = '<select><option value="0"></option>';

								for (var i = 0; i < classes.length; i++) {
									e += '<option value="' + (year + i);

									if (classes[i] == $(this).html()) {
										e += '" selected="selected';
									}

									e += '">' + classes[i] + '</option>';
								}

								e += '</select>';
							} else if ($(this).hasClass('col-position')) {
								e = '<textarea rows="2">' + $(this).html() + '</textarea>';
							} else if ($(this).hasClass('col-major')) {
								e = '<input type="text" value="' + $(this).children().map(function() {
									return $(this).html();
								}).get().join() + '" />';
							} else {
								e = 'text';

								if ($(this).hasClass('col-class') || $(this).hasClass('col-terms')) {
									e = 'number';
								} else if ($(this).hasClass('col-email')) {
									e = 'email';
								} else if ($(this).hasClass('col-phone')) {
									e = 'tel';
								}

								e = '<input type="' + e + '" value="' + $(this).html();

								if ($(this).hasClass('col-class')) {
									e += '" min="2000" max="' + (year + 4) + '" step="1';
								}

								e += '" />';
							}

							$(this).html(e);

							if ($(this).hasClass('col-major')) {
								$(this).children().selectize();
							}
						}
					}).parent().addClass('active');

					return false;
				}).on('click', '.save', function() {

				});
			});
		// ]]></script>
	</head>
	<body>
	    <div id="main">
			<h1>Hovselist</h1>
			<h2>Feel the Power</h2>
			<p></p>
			<table class="hovselist">
				<tr>
					<th><?
$cols = Mole::getFields();

echo implode(<<<EOF
</th>
					<th>
EOF
	, $cols);
?></th>
					<th>Major</th>
				</tr>
<?
$pdo = new PDO('sqlite:../hovselist.db');

$statement = 'SELECT `' . implode(<<<EOF
`,
	`
EOF
	, array_keys($cols)) . <<<EOF
`
FROM `moles`
WHERE `alley` <> 'Social'
EOF;

$result = $pdo->prepare($statement);
$result->execute();

while ($mole = $result->fetchObject('Mole')) {
	echo <<<EOF
				<tr id="u$mole->uid" class="form-control">

EOF;

	$majors = $mole->getMajors($pdo);

	foreach ($cols as $col => $label) {
		$val = $col == 'cohort' ? $mole->getCohort() : $mole->$col;

		echo <<<EOF
					<td class="col-$col">$val</td>

EOF;
	}

	echo <<<EOF
					<td class="col-major">

EOF;

	foreach ($majors as $short => $long) {
		echo <<<EOF
						<span class="maj-$short">$long</span>

EOF;
	}

	echo <<<EOF
					</td>
					<td>
						<a class="btn btn-sm edit" href="#">Edit</a>
						<a class="btn btn-sm save" href="#">Save</a>
					</td>
				</tr>

EOF;
}
?>			</table>
	    </div>
<?
print_footer(
	'Copyright &copy; 2016 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
