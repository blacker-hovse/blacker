<?
include(__DIR__ . '/../lib/include.php');
session_start();
$db = 'limbo.db';
$create = !file_exists($db);
$pdo = new PDO('sqlite:' . $db);
setlocale(LC_MONETARY, 'en_US.UTF-8');

if ($create) {
	$pdo->exec(<<<EOF
CREATE TABLE `users` (
	`id` integer PRIMARY KEY ASC,
	`name` varchar(64) UNIQUE NOT NULL,
	`email` varchar(255) UNIQUE NOT NULL,
	`balance` decimal(5) NOT NULL DEFAULT '0',
	`created` datetime NOT NULL
)
EOF
		);

	$pdo->exec(<<<EOF
CREATE TABLE `items` (
	`id` integer PRIMARY KEY ASC,
	`name` varchar(64) NOT NULL,
	`count` int NOT NULL,
	`user` int NOT NULL,
	`price` decimal NOT NULL,
	`tax` decimal NOT NULL DEFAULT '0',
	`description` varchar(255),
	`created` datetime NOT NULL
)
EOF
		);

	$pdo->exec(<<<EOF
CREATE TABLE `stock_changes` (
	`item` int NOT NULL,
	`count` int NOT NULL,
	`user` int NOT NULL,
	`updated` datetime NOT NULL
)
EOF
		);

	$pdo->exec(<<<EOF
CREATE TABLE `balance_changes` (
	`user` int NOT NULL,
	`amount` int NOT NULL,
	`updated` int NOT NULL
)
EOF
		);
}

if (isset($_POST['user'])) {
	$result = $pdo->prepare('SELECT * FROM `users` WHERE `name` = :user');

	$result->execute(array(
		':user' => $_POST['user']
	));

	$_SESSION = $result->fetch(PDO::FETCH_ASSOC);

	if (!$_SESSION) {
		session_destroy();
	}
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?
print_head('Limbo');
?>		<link rel="stylesheet" href="/lib/css/selectize.css" />
		<script type="text/javascript" src="/lib/js/jquery.min.js"></script>
		<script type="text/javascript" src="/lib/js/selectize.min.js"></script>
		<script type="text/javascript">// <![CDATA
			var items = [
<?
$result = $pdo->prepare('SELECT `name`, `count`, `price` FROM `items`');
$result->execute();
$rows = $result->fetchAll(PDO::FETCH_ASSOC);
$items = array();

foreach ($rows as $row) {
	if (!array_key_exists($row['name'], $items)) {
		$items[$row['name']] = array();
	}

	$price = (int) round($row['price'] * 100);

	if (!array_key_exists($price, $items[$row['name']])) {
		$items[$row['name']][$price] = 0;
	}

	$items[$row['name']][$price] += $row['count'];
}

foreach ($items as $i => $item) {
	$name = addslashes($i);
	$prices = $item;
	ksort($prices);
	$total = 0;

	echo <<<EOF
				{
					text: '$name',
					prices: {

EOF;

	foreach ($prices as $j => $count) {
		$price = $j / 100;
		$total += $count;

		echo <<<EOF
						$price: $count,

EOF;
	}

	echo <<<EOF
					},
					total: $total
				},

EOF;
}
?>			];

			var users = [
<?
$result = $pdo->prepare('SELECT `name` FROM `users`');
$result->execute();
$users = $result->fetchAll(PDO::FETCH_COLUMN);

foreach ($users as $user) {
	$name = addslashes($user);

	echo <<<EOF
				{
					text: '$name',
					value: '$name'
				},

EOF;
}
?>			];

			function limit(e) {
				var f = $(e).attr('min');
				var g = $(e).attr('max');

				if ($(e).val() < f) {
					$(e).val(f);
				} else if ($(e).val() > g) {
					$(e).val(g);
				}
			}

			$(function() {
				$('#purchase-count, #stock-count').change(function() {
					limit(this);
				});

				$('#user').selectize({
					create: true,
					maxItems: 1,
					options: users,
					render: {
						option_create: function(e, f) {
							return '<div class="create">Create user <b>' + f(e.input) + '</b>...</div>';
						}
					}
				});

				$('#purchase-item').selectize({
					maxItems: 1,
					onItemAdd: function(e, f) {
						e = f.data('count');
						$('#purchase-count').attr('max', e);
						limit('#purchase-count');
					},
					options: items,
					render: {
						item: function(e, f) {
							return '<div data-count="' + f(e.total) + '">' + f(e.text) + '</div>';
						},
						option: function(e, f) {
							var g = '';

							for (var i in e.prices) {
								g += ', ' + e.prices[i] + ' at $' + parseFloat(i).toFixed(2);
							}

							return '<div class="item"><span>' + f(e.text) + '</span><small>' + f(g.slice(2)) + '</small></div>'
						}
					},
					valueField: 'text'
				});

				$('#stock-item').selectize({
					create: true,
					maxItems: 1,
					options: items,
					valueField: 'text'
				});
			});
		// ]]></script>
	</head>
	<body>
	    <div id="main">
			<h1>Limbo 5</h1>
<?
$subtitles = array(
	'Caffeine is Life',
	'Free Market, Bitch',
	'Beats the C-Store',
	'Risen from the Ashes',
	'This Time It Works',
	'Vive le Capitalisme'
);

$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];

if (!$_SESSION) {
	echo <<<EOF
			<h2>$subtitle</h2>
			<p>Limbo is an honor code store. You are trusted to pay according to the listed price of any items you take from this store. Please record all transactions.</p>
			<h2>Login</h2>
			<form action="./" method="post">
				<div class="form-control">
					<label for="user">Username</label>
					<div class="input-group">
						<input type="text" id="user" name="user" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
			<h2>Donate</h2>
			<p>Enter the amount of money you physically deposited into the cash jar.</p>
			<form action="./" method="post">
				<div class="form-control">
					<label for="donation">Amount</label>
					<div class="input-group">
						<input type="text" id="donation" name="donation" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>

EOF;
} else {
	$name = htmlentities($_SESSION['name'], NULL, 'UTF-8');
	$balance = money_format('%.2n', abs($_SESSION['balance']));
	$payment = 0;

	if ($_SESSION['balance'] < 0) {
		$balance = '&minus;' . $balance;
		$payment = -$_SESSION['balance'];
	}

	echo <<<EOF
			<h2>$subtitle</h2>
			<p>Hello, <b>$name</b>. Your balance is <b>$balance</b>.</p>
			<form action="./" method="post">
				<p class="text-center">
					<input type="hidden" name="user" value="" />
					<input type="submit" class="btn-lg" value="Logout" />
				</p>
			</form>
			<h2>Make a Purchase</h2>
			<form action="./" method="post">
				<div class="form-control">
					<label for="purchase-item">Item</label>
					<div class="input-group input-group-left">
						<input type="text" id="purchase-item" name="purchase-item" />
					</div>
					<div class="input-group input-group-right">
						<input type="number" id="purchase-count" name="purchase-count" min="1" value="1" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
			<h2>Stock Limbo</h2>
			<form action="./" method="post">
				<div class="form-control">
					<label for="stock-item">Item</label>
					<div class="input-group input-group-left">
						<input type="text" id="stock-item" name="stock-item" maxlength="64" />
					</div>
					<div class="input-group input-group-right">
						<input type="number" id="stock-count" name="stock-count" min="1" value="1" />
					</div>
				</div>
				<div class="form-control">
					<label for="stock-price">Price and tax</label>
					<div class="input-group input-group-left">
						<input type="number" id="stock-price" name="stock-price" min="0" step="0.01" />
					</div>
					<div class="input-group input-group-right percent">
						<input type="number" id="stock-count" name="stock-count" min="0" max="99" value="5" />
					</div>
				</div>
				<div class="form-control optional">
					<label for="stock-notes">Notes</label>
					<div class="input-group">
						<textarea name="stock-notes" id="stock-notes" rows="4" maxlength="255"></textarea>
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
			<h2>Pay Limbo</h2>
			<p>Enter the amount of money you physically deposited into the cash jar.</p>
			<form action="./" method="post">
				<div class="form-control">
					<label for="deposit-amount">Amount</label>
					<div class="input-group">
						<input type="number" id="deposit-amount" name="deposit-amount" min="0" />
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
			<h2>Withdraw Money</h2>
			<p>Enter the amount of money you physically retrieved from the cash jar.</p>
			<form action="./" method="post">
				<div class="form-control">
					<label for="withdrawal-amount">Amount</label>
					<div class="input-group">
						<input type="number" id="withdrawal-amount" name="withdrawal-amount" min="0" />
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
?>		</div>
<?
print_footer(
	'Copyright &copy; 2015 Will Yu',
	'A service of Blacker House'
);
?>	</body>
</html>
