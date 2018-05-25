<?php
http_response_code(204);
$client_vars = ['uid', 'jpeg_url', 'png_url'];
require 'common.php';
init_mysql();
sql_insert_with_client_vars('png_versions');
