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
		<link href="//fonts.googleapis.com/css?family=Roboto:400,700|Oswald|Advent+Pro&amp;subset=latin,greek" rel="stylesheet" type="text/css" />
		<link href="/styles.css" rel="stylesheet" type="text/css" />
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
			<div>

EOF;

	$rss = new DOMDocument;
	$rss->load('http://blackerhovse.blogspot.com/feeds/posts/default?alt=rss');
	$items = $rss->getElementsByTagName('item');

	for ($i = 0; $i < $items->length and $i < 5; $i++) {
		$item = $items->item($i);
		$title = $item->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
		$date = strftime('%e %B %Y', strtotime($item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue));
		$link = $item->getElementsByTagName('link')->item(0)->firstChild->nodeValue;
		$media = $item->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'thumbnail');
		$media = $media->length ? $media->item(0)->getAttribute('url') : '';

		$description = implode('</p>
				<p>', array_slice(explode('
', preg_replace('/(<br\s*\/?>\s*)+/', '
', strip_tags(str_replace('>', '> ', $item->getElementsByTagName('description')->item(0)->firstChild->nodeValue), '<br><br/>')), 3), 0, -1));

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
