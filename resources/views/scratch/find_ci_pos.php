<?php
$content = file_get_contents('resources/views/dashboard.blade.php');
$pos = strpos($content, 'id="page-country-intelligence"');
if ($pos !== false) {
    echo "FOUND AT POS: " . $pos . "\n";
    echo "SUBSTRING:\n";
    echo substr($content, $pos - 50, 150) . "\n";
} else {
    echo "NOT FOUND\n";
}
