<?php

// Cargar .env local si existe (para desarrollo)
$envFile = dirname(__DIR__) . '/.env.local';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, ' "\'');
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Obtener DATABASE_URL de variables de entorno
$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

if (!$databaseUrl) {
    die("ERROR: DATABASE_URL no está configurada. Configúrala en .env.local o como variable de entorno.\n");
}

$parts = parse_url($databaseUrl);

if (!isset($parts['host']) || !isset($parts['user']) || !isset($parts['password'])) {
    die("ERROR: DATABASE_URL tiene un formato inválido.\n");
}

$pdo = new PDO(
    "mysql:host={$parts['host']};dbname=" . ltrim($parts['path'], '/'),
    $parts['user'],
    $parts['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);

$sql = "
    UPDATE app_group_recommendation
    SET status = 'closed'
    WHERE deadline < NOW()
    AND status = 'open'
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

echo "Filas afectadas: " . $stmt->rowCount() . PHP_EOL;
