<?php
// Path to the file to serve
$filename = "loader.lua";

// Make sure the file exists
if (!file_exists($filename)) {
    http_response_code(404);
    echo "File not found!";
    exit;
}

// Send headers so browser treats it as raw text
header("Content-Type: text/plain");
header("Content-Disposition: inline; filename=\"$filename\"");

// Output the file
readfile($filename);
?>
