<?
include(__DIR__ . '/../../lib/class/Mole.class.php');
include(__DIR__ . '/../../lib/include.php');
include(__DIR__ . '/../include.php');

$pdo = new PDO('sqlite:../hovselist.db');

if (array_key_exists('action', $_POST)) {
	header('HTTP/1.1 400 Bad Request');
	header('Status: 400 Bad Request');
	$content = 'Invalid action ' . htmlentities($_POST['action'], NULL, 'UTF-8') . '.';

	switch ($_POST['action']) {
		case 'insert':
			$mole = new Mole;

			foreach ($_POST as $field => $val) {
				if (property_exists('Mole', $field)) {
					$mole->$field = $val;
				}
			}

			$content = $mole->insert($pdo);
			break;
		case 'update':
			$mole = Mole::getMoleByUid($pdo, (int) $_POST['uid']);

			foreach ($_POST as $field => $val) {
				if (property_exists('Mole', $field . 'Bak')) {
					$mole->$field = $val;
				}
			}

			$content = $mole->update($pdo);
			break;
	}

	if (!$content) {
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
	}

	die($content);
}

$btns = '<a class="btn btn-sm edit" href="#">Edit</a><a class="btn btn-sm save" href="#">Save</a>';
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
echo implode("',
				'", Mole::getClasses());
?>'
			];

			var year = new Date();

			var majors = [
<?
$majors = Mole::getAllMajors($pdo);

foreach ($majors as $short => $long) {
	echo <<<EOF
				{
					text: '$long',
					value: '$short'
				},

EOF;
}
?>			];

			function edit(e) {
				e.each(function() {
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
							e += '" min="2000" max="' + (year + 4);
						}

						e += '" />';
					}

					$(this).html(e);

					if ($(this).hasClass('col-major')) {
						$(this).children().selectize({
							options: majors
						});
					}
				}).parent().addClass('active');
			}


			$(function() {
				$('.hovselist').on('click', '.edit', function() {
					edit($(this).parent().siblings().slice(1));
					return false;
				}).on('click', '.save', function() {
					var e = {
						action: $(this).parent().hasClass('add') ? 'insert' : 'update'
					};

					var g = $(this).parent().siblings();

					g.each(function() {
						var f = $(this).children().val();

						if (!f && $(this).hasClass('col-uid')) {
							f = $(this).text();
						}

						if ($(this).hasClass('col-terms')) {
							f = $.isNumeric(f) ? parseFloat(f) : '-';
						}

						e[this.className.slice(4)] = f;
					});

					$.post('./', e).done(function() {
						$('.error, .success').remove();
						$('h1').after('<div class="success">Successfully saved mole.</div>');
						e.cohort = g.filter('.col-cohort').find('option:selected').text();

						if (e.action == 'insert') {
							g.parent().clone().insertAfter(g.siblings('.add').html('<?
echo $btns;
?>').removeClass('add').parent()).children().each(function() {
								$(this).children().val('');
							});
						}

						g.each(function() {
							var f = e[this.className.slice(4)];

							if ($(this).hasClass('col-cohort')) {
								f = $(this).find('option:selected').text();
							}

							if ($(this).hasClass('col-major')) {
								$(this).html('<span>' + f.split(',').join('</span><span>') + '</span>');
							} else {
								$(this).text(f);
							}
						}).parent().removeClass('active');
					}).fail(function(e) {
						$('.error, .success').remove();
						$('h1').after('<div class="error">Failed to save mole: ' + e.responseText + '</div>');
						$(document).scrollTop(0);
					});

					return false;
				});

				edit($('.add').siblings());
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

echo implode("</th>
					<th>", $cols);
?></th>
				</tr>
<?
$statement = 'SELECT `' . implode('`,
	`', array_slice(array_keys($cols), 0, -1)) . '`
FROM `moles`';

$result = $pdo->prepare($statement);
$result->execute();

while ($mole = $result->fetchObject('Mole')) {
	echo <<<EOF
				<tr id="u$mole->uid" class="form-control">

EOF;

	$majors = $mole->getMajors($pdo);

	foreach ($cols as $col => $label) {
		if ($col == 'major') {
			$val = '';

			foreach ($majors as $short => $long) {
				$val .= <<<EOF
						<span class="maj-$short">$long</span>

EOF;
			}
		} elseif ($col == 'cohort') {
			$val = $mole->getCohort();
		} else {
			$val = $mole->$col;
		}

		echo <<<EOF
					<td class="col-$col">$val</td>

EOF;
	}

	echo <<<EOF
					<td>
						$btns
					</td>
				</tr>

EOF;
}
?>				<tr class="form-control">
<?
foreach ($cols as $col => $label) {
	echo <<<EOF
					<td class="col-$col"></td>
EOF;
}
?>					<td class="add">
						<a class="btn btn-sm save" href="#">Add</a>
					</td>
				</tr>
			</table>
	    </div>
<?
print_footer(
	'Copyright &copy; 2016 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
