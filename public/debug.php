<?php
echo "<h1>Render PHP Debug</h1>";
echo "<b>PHP Version:</b> " . phpversion() . "<br>";
echo "<b>APP_DEBUG:</b> " . var_export(getenv('APP_DEBUG'), true) . "<br>";
echo "<b>APP_ENV:</b> " . var_export(getenv('APP_ENV'), true) . "<br>";
echo "<b>DATABASE_URL:</b> " . (getenv('DATABASE_URL') ? 'EXISTS (HIDDEN)' : 'NOT FOUND') . "<br>";
echo "<hr>";
echo "<h2>_SERVER Environment</h2><pre>";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'URL') !== false || strpos($key, 'URL') !== false || strpos($key, 'SECRET') !== false || strpos($key, 'KEY') !== false || strpos($key, 'PASSWORD') !== false) {
        echo "$key: [HIDDEN]\n";
    } else {
        echo "$key: $value\n";
    }
}
echo "</pre>";
