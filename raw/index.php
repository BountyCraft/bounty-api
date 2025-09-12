<?php
// Set content type to plain text
header("Content-Type: text/plain");

// Read the file and output it
$filename = "loader.lua"; // your raw file
if (file_exists($filename)) {
    echo file_get_contents($filename);
} else {
    echo "File not found!";
}
?>
