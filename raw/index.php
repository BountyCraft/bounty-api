<?php
$filename = "loader.lua";
if (!file_exists($filename)) {
    http_response_code(404);
    echo "File not found!";
    exit;
}
header("Content-Type: text/plain"); 
readfile($filename);
?>
