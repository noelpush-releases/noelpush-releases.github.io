<?php
http_response_code(204);
$client_vars = ['version', 'url', 'mode', 'png_filesize', 'jpeg_filesize', 'width', 'height', 'upload_delay', 'total_delay', 'second_press_delay', 'third_press_delay', 'uid'];
require 'common.php';
init_mysql();
sql_insert_with_client_vars('uploads');
