<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentry</title>
    <style>
        body,
        body * {
            max-width: 98vw;
            text-wrap: wrap;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <pre>
    <?php

    require_once __DIR__ . '/../vendor/autoload.php';

    use Sentry\Sentry;

    // Enable error reporting
    echo "\n";
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        // Run with default settings
        new Sentry(dirname(__DIR__) . '\tmp', FALSE);

        // Success response
        echo "Success: Sentry completed successfully\n";
    }
    catch (\Exception $e) {
        // Error response
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
    ?>
    </pre>
</body>

</html>