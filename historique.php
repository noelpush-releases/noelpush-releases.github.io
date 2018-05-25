<?php
if (!isset($_COOKIE['uid'])) {
  ?>
<!doctype html>
<meta charset="utf-8">
<h3>Non-connecté.</h3>
<p>Vous n’êtes pas (ou plus) connecté. Pour vous connecter, veuillez cliquer sur <strong>Historique</strong> dans le menu de NoelPush.</p>
  <?php
  exit;
}
$uid = $_COOKIE['uid'];
if (strlen($uid) != 32) {
  exit;
}

require 'common.php';

init_mysql();

$images = [];
$sth = $dbh->prepare('SELECT logged_at, url, width, height FROM uploads WHERE uid = ? ORDER BY logged_at DESC');
$sth->execute([$uid]);
foreach ($sth as $row) {
  $images[] = [
    'date' => (int)$row['logged_at'],
    'url' => $row['url'],
    'width' => (int)$row['width'],
    'height' => (int)$row['height'],
  ];
}

if (!$images) {
  ?>
<!doctype html>
<meta charset="utf-8">
<h3>Aucune image :(</h3>
<p>Vous n’avez encore aucune image. Appuyez deux ou trois fois sur Impr Écran pour prendre votre première capture d’écran !</p>
  <?php
  exit;
}

function thumb($big, $width) {
  $small = $big;
  if ($width > 160) {
    $small = str_replace('/fichiers/', '/fichiers-xs/', $big);
  }
  return $small;
}

$mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

function format_date($timestamp) {
  global $mois;
  if (date('d-m-Y', $timestamp) == date('d-m-Y')) {
    return 'Aujourd’hui';
  }
  if (date('d-m-Y', $timestamp) == date('d-m-Y', time() - 60 * 60 * 24)) {
    return 'Hier';
  }
  if (date('d-m-Y', $timestamp) == date('d-m-Y', time() - 60 * 60 * 24 * 2)) {
    return 'Avant-hier';
  }
  $date = date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1];
  if (date('Y', $timestamp) != date('Y')) {
    $date .= ' ' . date('Y', $timestamp);
  }
  return $date;
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Historique NoelPush</title>
<style>
.body {
  background: hsl(262, 24%, 18%);
  font: 16px/1.4 Segoe UI, sans-serif;
  margin: 0;
  overflow-y: scroll;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  cursor: default;
  padding-left: 15px;
}

.body::-webkit-scrollbar {
  width: 18px;
  background: inherit;
}

.body::-webkit-scrollbar-thumb {
  background: hsl(262, 14%, 26%); /* 10% white */
}

.body--displaying::-webkit-scrollbar {
  background: hsl(260, 24%, 7%); /*rgba(0,0,0,.85) */
}

.body--displaying::-webkit-scrollbar-thumb {
  background: hsla(0, 0%, 100%, .05);
}

.body--displaying {
  background: hsl(262, 24%, 48%);
}

.day {
  color: hsla(262, 100%, 90%, .8);
  text-indent: 10px;
  margin: 15px 0 10px;
  font-weight: normal;
}

.item {
  display: inline-block;
}

.item__link {
  padding: 5px 12px;
  display: inline-block;
  vertical-align: middle;
  width: 160px;
  height: 120px;
  text-align: center;
  line-height: 120px;
}

.item__image {
  vertical-align: middle;
  max-height: 120px;
}

.item__link:hover {
  background: hsla(0, 0%, 100%, .075);
}

.list.showing {
  -webkit-filter: blur(10px) grayscale(50%);
}

.display {
  display: none;
}

.display--displaying {
  display: block;
  height: 200px;
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  z-index: 1;
  background: rgba(0,0,0,.85);
}

.display__thumb {
  position: fixed;
  display: block;
  background-position: center;
  background-size: cover;
}

.display__image {
  position: fixed;
}

.count {
  text-align: center;
  color: hsla(0, 0%, 100%, .75);
  padding: 40px 0 50px;
  font-size: 15px;
}
</style>

<body class="body">

<div class="display">
  <span class="display__thumb"></span>
  <a class="display__link" href="#"><img class="display__image" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="></a>
</div>

<div class="list">

<?php
$last_day = null;
foreach ($images as $image) {
  $day = date('d-m-Y', $image['date']);
  if ($day != $last_day): ?>
<h2 class="day"><?= format_date($image['date']) ?></h2>
<?php endif ?>
<div class="item">
  <a class="item__link" href="<?= $image['url'] ?>" target="_blank">
    <img class="item__image" src="<?= thumb($image['url'], $image['width']) ?>" data-thumb-url="<?= thumb($image['url'], $image['width']) ?>" data-url="<?= $image['url'] ?>" data-width=<?= $image['width'] ?> data-height=<?= $image['height'] ?>>
  </a>
</div><?php
  $last_day = $day;
}
?>

<div class="count"><?= count($images) ?> <?= count($images) > 1 ? 'captures' : 'capture' ?></div>
</div>

<script>
function qs(selector) {
  return document.querySelector(selector)
}

function qsa(selector) {
  return document.querySelectorAll(selector)
}

function getImageTarget(target) {
  if (target.nodeName != 'IMG') {
    target = target.firstElementChild
    console.log(target)
  }
  return target
}

function showImage(event) {
  if (event.which > 1 || event.metaKey || event.ctrlKey) {
    return
  }
  event.preventDefault()

  var image = getImageTarget(event.target)
    , thumbSrc = image.getAttribute('data-thumb-url')
    , src = image.getAttribute('data-url')
    , width = image.getAttribute('data-width')
    , height = image.getAttribute('data-height')

  qs('.display').className = 'display display--displaying'
  qs('.body').className = 'body body--displaying'
  qs('.list').className = 'list showing'

  var maxWidth = qs('.display').offsetWidth - 80
    , maxHeight = qs('.display').offsetHeight - 80

  if (width > maxWidth || height > maxHeight) {
    if (width > maxWidth) {
      height = height * maxWidth / width
      width = maxWidth
    }
    if (height > maxHeight) {
      width = width * maxHeight / height
      height = maxHeight
    }
  }

  qs('.display__link').href = src

  qs('.display__thumb').style.backgroundImage = 'url(' + thumbSrc + ')'
  qs('.display__thumb').style.width = width + 'px'
  qs('.display__thumb').style.height = height + 'px'
  qs('.display__thumb').style.left = 50 + ((maxWidth - width) / 2) + 'px'
  qs('.display__thumb').style.top = ((maxHeight + 80) - height) / 2 + 'px'

  qs('.display__link').innerHTML = '<img class="display__image" src="' + src + '" style="width: ' + width + 'px; height: ' + height + 'px; left: ' + (50 + ((maxWidth - width) / 2)) + 'px; top: ' + (((maxHeight + 80) - height) / 2) + 'px;">'
}

function hideDisplay(event) {
  if (event.target != qs('.display')) {
    return
  }

  qs('.display').className = 'display'
  qs('.body').className = 'body'
  qs('.list').className = 'list'
  qs('.display__link').innerHTML = ''
}

var images = qsa('.item__link');
for (var i = 0; i < images.length; i++) {
  images[i].addEventListener('click', showImage)
}

qs('.display').addEventListener('click', hideDisplay)
</script>

<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-76600300-1', 'auto');
ga('send', 'pageview');
</script>
