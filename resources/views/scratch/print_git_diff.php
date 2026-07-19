<?php
exec('git diff resources/views/dashboard.blade.php', $output);
echo implode("\n", $output) . "\n";
