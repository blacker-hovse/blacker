<?
$troll = false;
include(__DIR__ . '/lib/include.php');
include(__DIR__ . '/lib/md/Michelf/MarkdownExtra.inc.php');

$server = parse_url($_SERVER['REQUEST_URI']);
$page = 'error';

if ($server['path'] == '/') {
  $page = 'home';
} elseif (preg_match('/^\/[\w\/]+$/', $server['path'])) {
  if (substr($server['path'], -1) != '/') {
    header("Location: $server[path]/");
    die();
  }

  $path = strtr(substr($server['path'], 1, -1), '/', '.');

  if ($path != 'home' && file_exists(__DIR__ . "/src/$path.md")) {
    $page = $path;
  }
}

if ($page == 'error') {
  header('HTTP/1.1 404 Not Found');
  header('Status: 404 Not Found');
} else {
  header('HTTP/1.1 200 OK');
  header('Status: 200 OK');
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<?
print_head(ucwords(strtr($page, '_.', ' :')));
?>  </head>
  <body>
<?
print_header();
?>    <div id="main">
      <div>
<?
echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents(__DIR__ . "/src/$page.md"));
?>      </div>
<?
if ($page == 'home') {
  echo <<<EOF
      <h2>News</h2>
      <div class="feed">

EOF;

  $ch = curl_init('https://blacker.caltech.edu/history/?feed=rss2');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $rss = new DOMDocument;
  $rss->loadXML(curl_exec($ch));
  curl_close($ch);
  $items = $rss->getElementsByTagName('item');

  for ($i = 0; $i < $items->length and $i < 5; $i++) {
    $item = $items->item($i);
    $title = $item->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
    $date = strftime('%e %B %Y', strtotime($item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue));
    $link = $item->getElementsByTagName('link')->item(0)->firstChild->nodeValue;
    $media = $item->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'thumbnail');
    $media = $media->length ? $media->item(0)->getAttribute('url') : '';

    $description = str_replace('&#160;', '</p>
          <p>', $item->getElementsByTagName('description')->item(0)->firstChild->nodeValue);

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
?>    </div>
<?
print_footer(
  'Copyright &copy; 2005&ndash;2015 Blacker House',
  'California Institute of Technology'
);
?>  </body>
</html>
