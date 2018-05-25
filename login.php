<?php
if (!isset($_GET['token'])) {
  exit;
}
$token = $_GET['token'];
if (strlen($token) != 32) {
  exit;
}

require 'common.php';

init_mysql();

$sth = $dbh->prepare('SELECT uid, logged_at FROM login_tokens WHERE token = ?');
$sth->execute([$token]);
$result = $sth->fetch();

if (!$result) {
  exit('404');
}

$logged_at = (int)$result['logged_at'];
$uid = $result['uid'];

if ($logged_at < time() - 60 * 5) {
  ?>
<!doctype html>
<meta charset="utf-8">
<h3>Lien expiré.</h3>
<p>Les liens pour se connecter à votre historique NoelPush ne sont valables que 5 minutes, par mesure de sécurité.</p>
<p>Cliquez de nouveau sur <strong>Historique</strong> pour en générer un autre !</p>
  <?php
  exit;
}

setcookie('uid', $uid, time() + 60 * 60 * 24 * 365 * 10, '/', null, isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https', true);
header('Location: /historique');
