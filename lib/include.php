<?
function print_head($title = NULL) {
	if ($title) {
		$title .= ' - Blacker';
	} else {
		$title = 'Blacker';
	}

	echo <<<EOF
		<title>$title</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="//fonts.googleapis.com/css?family=Roboto:400,700|Oswald|Advent+Pro&amp;subset=latin,greek" rel="stylesheet" type="text/css" />
		<link href="/lib/css/blacker.css" rel="stylesheet" type="text/css" />
		<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />

EOF;
}

function print_header() {
	echo <<<EOF
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
					<a href="/tools/">Tools</a>
				</li>
			</ul>
			<div>
				<a href="/">
					<img src="/lib/crest.png" alt="Blacker" />
				</a>
			</div>
		</div>

EOF;
}

function print_footer($arg0, $arg1) {
	echo <<<EOF
		<div id="footer">
			<img src="/lib/lion.png" alt="" />
			<p>$arg0<br />$arg1<br />Website design by <a href="http://dt.clrhome.org/">DT</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:mole-imss@ugcs.caltech.edu">Contact</a></p>
			<h1>&gamma;&delta;&beta;&gamma;</h1>
		</div>

EOF;
}
?>
