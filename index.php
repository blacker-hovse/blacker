<?
include(__DIR__ . '/md/Michelf/MarkdownExtra.inc.php');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Blacker</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="http://fonts.googleapis.com/css?family=Roboto|Oswald|Advent+Pro&amp;subset=latin,greek" rel="stylesheet" type="text/css" />
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
				border-bottom: 1px solid #111;
				padding: 2em 10% 0.5em 10%;
				width: 80%;
				background-color: rgba(0, 0, 0, 0.9);
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
				color: #999;
				text-decoration: none;
				text-transform: uppercase;
				font-family: Oswald, sans-serif;
				letter-spacing: 0.3em;
			}
			#header a:hover {
				color: #fff;
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
			#footer {
				margin-top: 3em;
				border-top: 1px solid #111;
				padding: 2em 10%;
				color: #333;
				text-align: center;
				white-space: nowrap;
			}
			#footer > * {
				display: inline-block;
				margin: 0;
				border-left: 1px solid #111;
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
			h1 {
				margin-top: 0.5em;
				margin-bottom: 0.5em;
				color: #fff;
				font-family: Oswald, sans-serif;
				font-size: 3em;
				font-weight: normal;
			}
			h2 {
				margin-top: 0.5em;
				margin-bottom: 0.5em;
				color: #999;
				text-transform: uppercase;
				font-size: 1.5em;
				font-weight: normal;
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
				#main {
					padding-top: 12em;
				}
				#main > * {
					margin-left: 0;
					margin-right: 0;
				}
				#footer {
					margin: 0 -1em;
					padding: 1em 0;
				}
				.pull-left, .pull-right {
					float: none;
				}
			}
		</style>
	</head>
	<body>
		<div id="header">
			<ul class="pull-left">
				<li>
					<a href="/officers/">Officers</a>
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
					<img src="crest.png" alt="Blacker" />
				</a>
			</div>
		</div>
		<div id="main">
<?
echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents(__DIR__ . '/src/home.md'));
?>		</div>
		<div id="footer">
			<img src="lion.png" alt="" />
			<p>Copyright &copy; 2005&ndash;2015 Blacker House<br />California Institute of Technology</p>
			<h1>&gamma;&delta;&beta;&gamma;</h1>
		</div>
	</body>
</html>
