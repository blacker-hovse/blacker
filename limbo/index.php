<?
include(__DIR__ . '/../lib/include.php');
session_start();
$db = 'limbo.db';
$create = !file_exists($db);
$pdo = new PDO('sqlite:' . $db);
$error = false;
setlocale(LC_MONETARY, 'en_US.UTF-8');

function limbo_deposit($user, $amount) {
	global $pdo;

	$parameters = array(
		'amount' => $amount,
		':user' => $user
	);

	$result = $pdo->prepare('UPDATE `users` SET `balance` = `balance` + :amount WHERE `id` = :user');
	$result->execute($parameters);
	$result = $pdo->prepare("INSERT INTO `balance_changes` (`user`, `amount`, `updated`) VALUES (:user, :amount, DATETIME('now'))");
	$result->execute($parameters);
}

function limbo_stock_part($item, $count, $user) {
	global $pdo;

	$result = $pdo->prepare("INSERT INTO `stock_changes` (`item`, `count`, `user`, `updated`) VALUES(:item, :count, :user, DATETIME('now'))");

	$result->execute(array(
		':item' => $item,
		':count' => $count,
		':user' => $user
	));
}

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

if (array_key_exists('user', $_POST)) {
	$user = trim($_POST['user']);
	$email = array_key_exists('email', $_POST) ? trim($_POST['email']) : false;

	if ($email) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$result = $pdo->prepare('SELECT `name` FROM `users` WHERE `email` = :email');

			$result->execute(array(
				':email' => $email
			));

			if ($name = $result->fetch(PDO::FETCH_COLUMN)) {
				$error = "User <b>$name</b> exists with this email address.";
			} elseif ($user) {
				if (preg_match('/^[\w ]+$/', $user)) {
					$result = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `created`) VALUES (:user, :email, DATETIME('now'))");

					$result->execute(array(
						':user' => $user,
						':email' => $email
					));

					$_SESSION = array(
						'name' => $user,
						'email' => $email,
						'balance' => 0
					);
				} else {
					$error = 'Usernames may only contain letters, numbers, and spaces.';
				}
			} else {
				$error = 'No user exists with this email address.';
			}
		} else {
			$error = 'Invalid email address.';
		}
	} else {
		$result = $pdo->prepare('SELECT * FROM `users` WHERE `name` = :user');

		$result->execute(array(
			':user' => $user
		));

		$_SESSION = $result->fetch(PDO::FETCH_ASSOC);

		if (!$_SESSION) {
			session_destroy();

			if ($user) {
				$error = 'Username not found.';
			}
		}
	}
}

if (array_key_exists('purchase-item', $_POST)) {
	$count = (int) $_POST['purchase-count'];
	$total = 0;
	$result = $pdo->prepare('SELECT * FROM `items` WHERE `name` = :item ORDER BY `price`');

	$result->execute(array(
		':item' => $_POST['purchase-item']
	));

	while ($count and $item = $result->fetch(PDO::FETCH_ASSOC)) {
		if ($count < $item['count']) {
			$update = $pdo->prepare('UPDATE `items` SET `count` = `count` - :count WHERE `id` = :id');

			$update->execute(array(
				':count' => $count,
				':id' => $item['id']
			));

			$cost = $count * $item['price'];
			$total += $cost;
			limbo_stock_part($item['id'], -$count, $_SESSION['id']);
			$count = 0;
		} else {
			$update = $pdo->prepare('DELETE FROM `items` WHERE `id` = :id');

			$update->execute(array(
				':id' => $item['id']
			));

			$cost = $item['count'] * $item['price'];
			$total += $cost;
			limbo_stock_part($item['id'], -$item['count'], $_SESSION['id']);
			$count -= $item['count'];
		}

		$cost = round($cost * (1 - $item['tax']), 2);
		limbo_deposit($item['user'], $cost);

		if ($item['user'] == $_SESSION['id']) {
			$_SESSION['balance'] += $cost;
		}
	}

	$_SESSION['balance'] -= $total;
	limbo_deposit($_SESSION['id'], -$total);
}

if (array_key_exists('stock-item', $_POST)) {
	$name = htmlentities($_POST['stock-item'], NULL, 'UTF-8');
	$count = (int) $_POST['stock-count'];
	$price = round(max($_POST['stock-price'], 0), 2);
	$tax = (int) min(max($_POST['stock-tax'], 0), 99) / 100;
	$description = htmlentities($_POST['stock-notes'], NULL, 'UTF-8');
	$result = $pdo->prepare('SELECT * FROM `items` WHERE `name` = :item AND `user` = :user');

	$result->execute(array(
		':item' => $name,
		':user' => $_SESSION['id']
	));

	if ($item = $result->fetch(PDO::FETCH_ASSOC)) {
		$result = $pdo->prepare('UPDATE `items` SET `count` = `count` + :count, `price` = :price, `tax` = :tax, `description` = :description WHERE `name` = :item AND `user` = :user');
	} else {
		$result = $pdo->prepare("INSERT INTO `items` (`name`, `count`, `user`, `price`, `tax`, `description`, `created`) VALUES (:item, :count, :user, :price, :tax, :description, DATETIME('now'))");
	}

	$result->execute(array(
		':count' => $count,
		':price' => $price,
		':tax' => $tax,
		':description' => $description,
		':item' => $name,
		':user' => $_SESSION['id']
	));

	$result = $pdo->prepare('SELECT `id` FROM `items` WHERE `name` = :item AND `user` = :user');

	$result->execute(array(
		':item' => $name,
		':user' => $_SESSION['id']
	));

	limbo_stock_part($result->fetch(PDO::FETCH_COLUMN), $count, $_SESSION['id']);
}

if (array_key_exists('deposit-amount', $_POST)) {
	limbo_deposit($_SESSION['id'], round(max($_POST['deposit-amount'], 0), 2));
}

if (array_key_exists('withdrawal-amount', $_POST)) {
	limbo_deposit($_SESSION['id'], -round(max($_POST['deposit-amount'], 0), 2));
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
$result = $pdo->prepare('SELECT `name`, `count`, `price` FROM `items` ORDER BY `name`, `price`');
$result->execute();
$items = array();

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
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
	$total = 0;

	echo <<<EOF
				{
					text: '$name',
					values: [

EOF;

	foreach ($item as $j => $count) {
		$price = $j / 100;
		$total += $count;

		echo <<<EOF
						{
							price: $price,
							count: $count
						},

EOF;
	}

	echo <<<EOF
					],
					total: $total
				},

EOF;
}
?>			];

			var users = [
<?
$result = $pdo->prepare('SELECT `name` FROM `users`');
$result->execute();

while ($user = $result->fetch(PDO::FETCH_COLUMN)) {
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

							for (var i = 0; i < e.values.length; i++) {
								g += ', ' + e.values[i].count + ' at $' + parseFloat(e.values[i].price).toFixed(2);
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
if ($error) {
	echo <<<EOF
			<div class="error">$error</div>

EOF;
}

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
			<p>Limbo is an honor code store. To create an account, please provide your email.</p>
			<form action="./" method="post">
				<div class="form-control">
					<label for="user">Username</label>
					<div class="input-group">
						<input type="text" id="user" name="user" />
					</div>
				</div>
				<div class="form-control optional">
					<label for="email">Email</label>
					<div class="input-group">
						<input type="email" id="email" name="email" />
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
						<input type="number" id="stock-tax" name="stock-tax" min="0" max="99" value="5" />
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
