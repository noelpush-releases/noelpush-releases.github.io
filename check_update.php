<?php
$CURRENT_VERSION = 17;
$client_vars = ['current_version', 'uid'];
require 'common.php';
init_mysql();
sql_insert_with_client_vars('update_checks');
echo $current_version == $CURRENT_VERSION ? '0' : '1';
