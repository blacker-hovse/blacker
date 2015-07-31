<?
include(__DIR__ . '/md/Michelf/MarkdownExtra.inc.php');
$server = parse_url($_SERVER['REQUEST_URI']);
$page = 404;

if (preg_match('/^\/(\w+)(\/?)(\?.*)?$/', $server['path'], $matches)) {
	if (!$matches[2]) {
		header("Location: $server[path]/");
		die();
	} elseif ($matches[1] != 'home' && file_exists(__DIR__ . "/src/$matches[1].md")) {
		$page = $matches[1];
	}
} elseif ($server['path'] == '/') {
	$page = 'home';
}

if ($page == 404) {
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
} else {
	header('HTTP/1.1 200 OK');
	header('Status: 200 OK');
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Blacker</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="http://fonts.googleapis.com/css?family=Roboto:400,700|Oswald|Advent+Pro&amp;subset=latin,greek" rel="stylesheet" type="text/css" />
		<style type="text/css">
			body {
				margin: 0;
				background-color: #000;
				color: #ccc;
				font-family: Roboto, sans-serif;
				font-size: 20px;
				line-height: 2;
			}
			#header {
				position: fixed;
				z-index: 9;
				border-bottom: 1px solid #111;
				padding: 2em 10% 0.5em 10%;
				width: 80%;
				background-color: rgba(0, 0, 0, 0.8);
				text-align: center;
			}
			#header ul {
				list-style-type: none;
				margin: 0;
				padding: 0;
				width: 40%;
			}
			#header li {
				float: left;
				width: 50%;
			}
			#header a {
				padding: 0.5em;
				text-decoration: none;
				text-transform: uppercase;
				font-family: Oswald, sans-serif;
				letter-spacing: 0.3em;
			}
			#header img {
				margin-top: -1.5em;
				width: 4em;
			}
			#main {
				padding-top: 6em;
			}
			#main > * {
				margin-left: 20%;
				margin-right: 20%;
			}
			#main img.pull-right {
				margin: 0 0 1em 2em;
				padding: 0.5em;
				width: 5.4em;
				background-color: #111;
				opacity: 0.8;
			}
			#footer {
				margin-top: 3em;
				border-top: 1px solid #111;
				padding: 2em 10%;
				color: #666;
				text-align: center;
				white-space: nowrap;
			}
			#footer > * {
				display: inline-block;
				margin: 0;
				border-left: 1px solid #222;
				padding: 0 2em;
				height: 6em;
				vertical-align: middle;
				text-align: left;
				font-size: 0.6em;
			}
			#footer > :first-child {
				border-left: 0;
			}
			#footer img {
				height: 5em;
				opacity: 0.5;
			}
			#footer h1 {
				padding: 0 0.5em;
				height: 1.2em;
				color: #111;
				font-family: 'Advent Pro', sans-serif;
				font-size: 3em;
				line-height: 1;
				letter-spacing: 0.2em;
			}
			.pull-left {
				float: left;
			}
			.pull-right {
				float: right;
			}
			.channel {
				padding-left: 2em;
			}
			.item {
				position: relative;
				border-left: 0.3em solid #111;
				padding: 1em 0 1em 4em;
				font-size: 0.9em;
			}
			.thumbnail {
				position: absolute;
				top: 1.2em;
				left: -2.4em;
				border-radius: 3em;
				padding: 0.5em;
				width: 3.6em;
				height: 3.6em;
				background-color: #111;
			}
			h1 {
				margin-top: 0.5em;
				margin-bottom: 0;
				color: #fff;
				text-align: center;
				font-family: Oswald, sans-serif;
				font-size: 3em;
				font-weight: normal;
			}
			h2 {
				margin: 1em 20% 0 20%;
				border-top: 1px solid #222;
				padding-top: 1em;
				color: #999;
				text-align: center;
				text-transform: uppercase;
				font-size: 1.5em;
				font-weight: normal;
				letter-spacing: 0.2em;
			}
			h3 {
				font-size: 1.2em;
			}
			small {
				padding-left: 1em;
				color: #999;
			}
			table {
				width: 100%;
				font-size: 0.9em;
				line-height: 1;
			}
			td {
				padding: 0.5em;
			}
			td:first-child {
				width: 40%;
				color: #fff;
				text-align: right;
			}
			a {
				color: #999;
			}
			a:hover {
				color: #fff;
			}
			@media screen and (max-width: 960px) {
				body {
					font-size: 16px;
				}
				#main > * {
					margin-left: 10%;
					margin-right: 10%;
				}
			}
			@media screen and (max-width: 640px) {
				body {
					font-size: 12px;
				}
			}
			@media screen and (max-width: 480px) {
				#header {
					position: static;
					padding: 1em 0;
					width: 100%;
				}
				#header ul {
					width: auto;
				}
				#header li {
					margin: 1em 0;
					width: 50%;
				}
				.pull-left, .pull-right {
					float: none;
				}
				#main {
					padding-top: 0;
				}
				#main > * {
					margin-left: 0;
					margin-right: 0;
				}
				#footer {
					margin: 0 -1em;
					padding: 1em 0;
				}
			}
		</style>
	</head>
	<body>
		<div id="header">
			<ul class="pull-left">
				<li>
					<a href="/people/">People</a>
				</li>
				<li>
					<a href="/gallery/">Gallery</a>
				</li>
			</ul>
			<ul class="pull-right">
				<li>
					<a href="/quotes/">Quotes</a>
				</li>
				<li>
					<a href="/wiki/">Wiki</a>
				</li>
			</ul>
			<div>
				<a href="/">
					<img src="/crest.png" alt="Blacker" />
				</a>
			</div>
		</div>
		<div id="main">
			<div>
<?
echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents(__DIR__ . "/src/$page.md"));
?>			</div>
<?
if ($page == 'home') {
	echo <<<EOF
			<h2>News</h2>
			<div class="channel">

EOF;

	$rss = new DOMDocument;
	$rss->load('http://blackerhovse.blogspot.com/feeds/posts/default?alt=rss');
	$items = $rss->itemElementsByTagName('item');

	for ($i = 0; $i < $items->length and $i < 5; $i++) {
		$item = $items->item($i);
		$title = $item->itemElementsByTagName('title')->item(0)->firstChild->nodeValue;
		$date = strftime('%e %B %Y', strtotime($item->itemElementsByTagName('pubDate')->item(0)->firstChild->nodeValue));
		$link = $item->itemElementsByTagName('link')->item(0)->firstChild->nodeValue;
		$media = $item->itemElementsByTagNameNS('http://search.yahoo.com/mrss/', 'thumbnail');
		$media = $media->length ? $media->item(0)->itemAttribute('url') : '';

		$description = implode('</p>
				<p>', array_slice(explode('
', preg_replace('/(<br\s*\/?>\s*)+/', '
', strip_tags(str_replace('>', '> ', $item->itemElementsByTagName('description')->item(0)->firstChild->nodeValue), '<br><br/>')), 3), 0, -1));

		echo <<<EOF
				<div class="item">
					<h3>
						<a href="$link">$title</a>
						<small>$date</small>
					</h3>
					<img class="thumbnail" src="$media" alt="" />
					<p>$description</p>
				</div>

EOF;
	}

	echo <<<EOF
			</div>

EOF;
}
?>		</div>
		<div id="footer">
			<img src="/lion.png" alt="" />
			<p>Copyright &copy; 2005&ndash;2015 Blacker House<br />California Institute of Technology<br />Website design by <a href="http://dt.clrhome.org/">DT</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:mole-imss@ugcs.caltech.edu">Contact</a></p>
			<h1>&gamma;&delta;&beta;&gamma;</h1>
		</div>
	</body>
</html>
