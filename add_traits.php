<?php

$dir = __DIR__ . '/app/Models';
$files = glob($dir . '/*.php');

$exclude = ['SystemActivityLog.php', 'AuditLog.php'];

foreach ($files as $file) {
    $basename = basename($file);
    if (in_array($basename, $exclude)) {
        continue;
    }

    $content = file_get_contents($file);

    // Skip if already added
    if (strpos($content, 'App\Traits\LogsActivity') !== false) {
        continue;
    }

    // Add use statement at the top
    $content = preg_replace(
        '/(namespace App\\\\Models;.*?)(class)/s',
        "$1use App\\Traits\\LogsActivity;\n\n$2",
        $content
    );

    // Add use trait inside class
    $content = preg_replace(
        '/(class [a-zA-Z0-9_]+\s*(?:extends [a-zA-Z0-9_]+)?\s*(?:implements [a-zA-Z0-9_, ]+)?\s*\{)/s',
        "$1\n    use LogsActivity;\n",
        $content
    );

    file_put_contents($file, $content);
    echo "Added LogsActivity to $basename\n";
}
echo "Done.\n";
