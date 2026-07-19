<?php
$content = file_get_contents('resources/views/dashboard.blade.php');
$matches = [];
preg_match_all('/id="page-[a-z-]+"/i', $content, $matches);
print_r($matches[0]);
