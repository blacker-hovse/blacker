<?
include(__DIR__ . '/../../lib/class/Mole.class.php');
include(__DIR__ . '/../../lib/include.php');
include(__DIR__ . '/../include.php');

function hovselist_position($roles, $moles) {
	$positions = array_map(function() {
		return array();
	}, $roles);

	foreach ($moles as $mole) {
		$position = explode(',', strtolower(preg_replace('/[^\w,]+/', '', $mole['position'])));

		foreach ($roles as $list => $titles) {
			if (array_intersect($titles, $position)) {
				$positions[$list][] = $mole['format'];
			}
		}
	}

	return $positions;
}

function hovselist_write($list, $moles) {
	$handle = popen(__DIR__ . '/mailingset write mole-' . $list, 'w');

	if (!$handle) {
		throw new UnexpectedValueException($list);
	}

	foreach ($moles as $mole) {
		fwrite($handle, $mole . "\n");
	}

	if (pclose($handle)) {
		throw new UnexpectedValueException($list);
	}
}

$alleys = "('" . implode("', '", array_diff(get_alleys(), array(
	'Fort Knight',
	'Munth'
))). "')";

$roles = array(
	'offices' => array(
		'athteam' => array(
			'athteam'
		),
		'damage' => array(
			'damagecontrol'
		),
		'historians' => array(
			'historian'
		),
		'imss' => array(
			'headimssrep',
			'imssrep'
		),
		'librarians' => array(
			'librarian'
		),
		'socteam' => array(
			'socteam'
		)
	),
	'people' => array(
		'arc' => array(
			'arcrep'
		),
		'boc' => array(
			'bocrep'
		),
		'bookie' => array(
			'bookie'
		),
		'crc' => array(
			'crcrep'
		),
		'pope' => array(
			'pope'
		),
		'president' => array(
			'president'
		),
		'secretary' => array(
			'secretary'
		),
		'treasurer' => array(
			'treasurer'
		),
		'vp' => array(
			'vicepresident'
		)
	),
	'support' => array(
		'healthad' => array(
			'healthad'
		),
		'ra-prime' => array(
			'ra'
		),
		'ucc-prime' => array(
			'coheaducc',
			'ucc'
		)
	)
);

$order = 'ORDER BY `class`, `name`';
$president = 'President <mole-president@blacker.caltech.edu>';
$secretary = 'Secretary <mole-secretary@blacker.caltech.edu>';
$pdo = new PDO('sqlite:../hovselist.db');
$year = date('Y') + (date('n') >= 7);

if (array_key_exists('action', $_POST)) {
	header('HTTP/1.1 400 Bad Request');
	header('Status: 400 Bad Request');
	$content = '';
	$format = "`name` || ' ''' || SUBSTR(`class`, 3) || ' <' || `email` || '>'";
	$lists = array();

	try {
		switch ($_POST['action']) {
			case 'delete':
				$content = Mole::killMoleByUid($pdo, (int) $_POST['uid']);
				break;
			case 'gen_class':
				$result = $pdo->prepare(<<<EOF
SELECT `class`,
	$format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `class` <> ''
	AND `class` >= $year
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$rows = $result->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

				foreach ($rows as $list => $moles) {
					$moles[] = $president;
					$moles[] = $secretary;
					hovselist_write($list, $moles);
					$lists[] = 'mole-' . $list;
				}

				break;
			case 'gen_cohort':
				$result = $pdo->prepare(<<<EOF
SELECT `cohort`,
	$format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `cohort` <> ''
	AND `cohort` >= $year
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$rows = $result->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

				foreach ($rows as $class => $moles) {
					$list = strtolower(Mole::yearToCohort($class));

					if ($list != 'frosh') {
						$list .= 's';
					} else {
						$moles[] = <<<EOF
mole-permafrosh <mole-permafrosh@blacker.caltech.edu>

EOF;
					}

					$moles[] = $president;
					$moles[] = $secretary;
					hovselist_write($list, $moles);
					$lists[] = 'mole-' . $list;
				}

				break;
			case 'gen_location':
				$result = $pdo->prepare(<<<EOF
SELECT $format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `alley` IN $alleys
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_COLUMN);
				$moles[] = $president;
				$moles[] = $secretary;
				hovselist_write('oncampus', $moles);
				$lists[] = 'mole-oncampus';

				$result = $pdo->prepare(<<<EOF
SELECT $format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `alley` NOT IN $alleys
	AND `alley` <> 'Munth'
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_COLUMN);
				$moles[] = $president;
				$moles[] = $secretary;
				hovselist_write('offcampus', $moles);
				$lists[] = 'mole-offcampus';

				$result = $pdo->prepare(<<<EOF
SELECT $format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `alley` = 'Munth'
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_COLUMN);
				$moles[] = $president;
				$moles[] = $secretary;
				hovselist_write('munth-prime', $moles);
				$lists[] = 'mole-munth';
				break;
			case 'gen_mole':
				$result = $pdo->prepare(<<<EOF
SELECT $format
FROM `moles`
WHERE `alley` <> 'Social'
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_COLUMN);
				hovselist_write('full-prime', $moles);
				$lists[] = 'mole-full';

				$result = $pdo->prepare(<<<EOF
SELECT $format
FROM `moles`
WHERE `alley` = 'Social'
	AND `position` <> 'RA'
$order
EOF
					);

				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_COLUMN);
				hovselist_write('social-prime', $moles);
				$lists[] = 'mole-social';
				break;
			case 'gen_offices':
			case 'gen_people':
			case 'gen_support':
				$result = $pdo->prepare(<<<EOF
SELECT `position`,
	$format AS `format`
FROM `moles`
WHERE `alley` <> 'Social'
$order
EOF
					);

				$set = substr($_POST['action'], 4);
				$result->execute();
				$moles = $result->fetchAll(PDO::FETCH_ASSOC);
				$positions = hovselist_position($roles[$set], $moles);

				foreach ($positions as $list => $moles) {
					if ($set == 'offices' or $list == 'healthad') {
						$moles[] = $president;
						$moles[] = $secretary;
					}

					hovselist_write($list, $moles);
					$lists[] = 'mole-' . $list;
				}

				break;
			case 'restart_mailingset':
				exec(__DIR__ . '/mailingset restart', $output, $fail);
				$content = $fail ? 'Failed to restart Mailingset.' : 'Successfully restarted Mailingset.';
				break;
			case 'insert':
				$mole = new Mole;

				foreach ($_POST as $field => $val) {
					if (property_exists('Mole', $field)) {
						$mole->$field = $val;
					}
				}

				$content = $mole->insert($pdo);

				if (!$content and array_key_exists('major', $_POST)) {
					$content = $mole->setMajors($pdo, explode(',', $_POST['major']));
				}

				break;
			case 'update':
				$mole = Mole::getMoleByUid($pdo, (int) $_POST['uid']);

				foreach ($_POST as $field => $val) {
					if (property_exists('Mole', $field . 'Bak')) {
						$mole->$field = $val;
					}
				}

				$content = $mole->update($pdo);

				if (!$content and array_key_exists('major', $_POST)) {
					$majors = explode(',', $_POST['major']);
					$majors_bak = array_keys($mole->getMajors($pdo));

					if (count($majors) != count($majors_bak) or array_diff($majors, $majors_bak)) {
						$content = $mole->setMajors($pdo, $majors);
					}
				}

				break;
			default:
				throw new OutOfBoundsException;
		}

		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
	} catch (OutOfBoundsException $e) {
		$content = 'Invalid action ' . htmlentities($_POST['action'], NULL, 'UTF-8') . '.';
	} catch (UnexpectedValueException $e) {
		$content = 'Failed to generate ' . $e->getMessage() . '.';
	}

	if ($lists) {
		$content .= ' Successfully generated ' . implode(', ', $lists) . '.';
	}

	die($content);
}

$btns = '<a class="btn btn-sm edit" href="#">Edit</a><a class="btn btn-sm del" href="#">Delete</a><a class="btn btn-sm save" href="#">Save</a>';
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

			var year = <?
echo $year;
?>;

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
							return this.className.slice(10);
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
							options: majors,
							searchField: ['text', 'value']
						});
					}
				}).parent().addClass('active');
			}

			function fail(e) {
				$('.error, .success').remove();
				$('#main h1').after('<div class="error">Action failed: ' + e.responseText + '</div>');
				$(document).scrollTop(0);
			}

			$(function() {
				$('.hovselist').on('click', '.edit', function() {
					edit($(this).parent().siblings().slice(1));
					return false;
				}).on('click', '.del', function() {
					if (confirm('Are you sure you want to delete this mole?')) {
						var g = $(this).parent().parent();

						$.post('./', {
							action: 'delete',
							uid: g.children('.col-uid').text()
						}).done(function() {
							g.remove();
							$('.error, .success').remove();
							$('#main h1').after('<div class="success">Successfully deleted mole.</div>');
						}).fail(fail);
					}

					return false;
				}).on('click', '.save', function() {
					var e = {
						action: $(this).parent().hasClass('add') ? 'insert' : 'update'
					};

					var g = $(this).parent().siblings();

					g.each(function() {
						var f = $(this).children().val();

						if ($(this).hasClass('col-uid')) {
							f = f ? parseInt(f) : $(this).text();
						}

						if ($(this).hasClass('col-terms')) {
							f = $.isNumeric(f) ? parseFloat(f) : '-';
						}

						e[this.className.slice(4)] = f;
					});

					$.post('./', e).done(function() {
						$('.error, .success').remove();
						$('#main h1').after('<div class="success">Successfully saved mole.</div>');
						e.cohort = g.filter('.col-cohort').find('option:selected').text();

						if (e.action == 'insert') {
							edit(g.parent().clone().insertAfter(g.siblings('.add').html('<?
echo $btns;
?>').removeClass('add').parent()).children(':not(.add)').empty());
						}

						g.each(function() {
							var f = e[this.className.slice(4)];

							if ($(this).hasClass('col-cohort')) {
								f = $(this).find('option:selected').text();
							}

							if ($(this).hasClass('col-major')) {
								$(this).html(f.split(',').map(function(e) {
									var f = '';

									for (var i = 0; i < majors.length; i++) {
										if (majors[i].value == e) {
											f = majors[i].text;
										}
									}

									return '<span class="col-major-' + e + '">' + f + '</span>';
								}).join(''));
							} else {
								$(this).text(f);
							}
						}).parent().removeClass('active');
					}).fail(fail);

					return false;
				});

				$('.gen').click(function() {
					$.post('./', {action: this.id.replace('-', '_')}).done(function(e) {
						$('.error, .success').remove();
						$('#main h1').after('<div class="success">' + e + '</div>');
						$(document).scrollTop(0);
					}).fail(fail);

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
			<p class="text-center">
				<a id="gen-class" class="btn btn gen" href="#">Generate Class Lists</a>
				<a id="gen-cohort" class="btn btn gen" href="#">Generate Cohort Lists</a>
				<a id="gen-location" class="btn btn gen" href="#">Generate Location Lists</a>
				<a id="gen-offices" class="btn btn gen" href="#">Generate Team Lists</a>
				<a id="gen-people" class="btn btn gen" href="#">Generate Office Lists</a>
				<a id="gen-support" class="btn btn gen" href="#">Generate Support Lists</a>
				<a id="gen-mole" class="btn btn gen" href="#">Generate Membership Lists</a>
				<a id="restart-mailingset" class="btn btn gen" href="#">Restart Mailingset</a>
			</p>
			<table class="hovselist">
				<tr>
					<th><?
$cols = Mole::getFields();

echo implode("</th>
					<th>", $cols);
?></th>
				</tr>
<?
$result = $pdo->prepare('SELECT `' . implode('`, `', array_slice(array_keys($cols), 0, -1)) . '` FROM `moles` ORDER BY `name`');
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
						<span class="col-major-$short">$long</span>

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
