<?php
require '../config.php';

$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];

if (isset($client_vars)) {
  foreach ($client_vars as $var) {
    if (!isset($_POST[$var])) {
      exit;
    }
    ${$var} = $_POST[$var];
  }
}

function init_mysql() {
  global $dbh;
  try {
    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
  }
  catch (PDOException $e) {
    exit("Probl&egrave;me avec MySQL.");
  }
}

function sql_insert_with_client_vars($table) {
  global $dbh, $client_vars, $ip;

  $sql_query = 'INSERT INTO ' . $table . '(ip, logged_at';
  foreach ($client_vars as $var) {
    $sql_query .= ', ' . $var;
  }
  $sql_query .= ') VALUES(:ip, :logged_at';
  foreach ($client_vars as $var) {
    $sql_query .= ', :' . $var;
  }
  $sql_query .= ')';

  $sql_arguments = [
    ':ip' => $ip,
    ':logged_at' => time(),
  ];
  foreach ($client_vars as $var) {
    $sql_arguments[':' . $var] = $GLOBALS[$var];
  }

  $req = $dbh->prepare($sql_query);
  $ret = $req->execute($sql_arguments);
}
