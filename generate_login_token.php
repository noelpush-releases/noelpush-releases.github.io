<?php
if (!isset($_POST['uid'])) {
  exit;
}
$uid = $_POST['uid'];
if (strlen($uid) != 32) {
  exit;
}

require 'common.php';

init_mysql();

$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
$token = '';
for ($i = 0; $i < 32; $i++) {
  $token .= $chars[mt_rand(0, strlen($chars) - 1)];
}

$req = $dbh->prepare('INSERT INTO login_tokens(logged_at, uid, token, ip) VALUES(?, ?, ?, ?)');
$ret = $req->execute([time(), $uid, $token, $ip]);

echo $token;
