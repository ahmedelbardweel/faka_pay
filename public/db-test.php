<?php
echo "<h1>Database Connection Test</h1>";

$url = getenv('DATABASE_URL');
if (!$url) {
    die("❌ DATABASE_URL is not set in environment.");
}

echo "Found DATABASE_URL. Attempting to parse...<br>";

$db_config = parse_url($url);

if (!$db_config) {
    die("❌ Failed to parse DATABASE_URL.");
}

$host = $db_config['host'];
$port = $db_config['port'] ?? 5432;
$database = ltrim($db_config['path'], '/');
$user = $db_config['user'];
$pass = $db_config['pass'];

echo "<b>Host:</b> $host<br>";
echo "<b>Port:</b> $port<br>";
echo "<b>Database:</b> $database<br>";
echo "<b>User:</b> $user<br>";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Successfully connected to the database!<br>";

    $query = $pdo->query("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "⚠️ Database is connected but NO TABLES found. Migrations might have failed.<br>";
    } else {
        echo "✅ Tables found: " . implode(', ', $tables) . "<br>";
    }

} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "<br>";
}
