<?php

// Path to the Laravel application
$laravelPath = '/domains/cbt.marafco.com/public_html';

// Change directory to the Laravel application path
chdir($laravelPath);

// Define the Artisan commands to be executed
$commands = [
    'php artisan cache:clear',
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:clear',
];

foreach ($commands as $command) {
    // Execute the Artisan command
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    // Output the result of the command (for debugging purposes)
    echo "Command: $command\n";
    echo "Return Code: $returnVar\n";
    echo "Output:\n" . implode("\n", $output) . "\n\n";
}
