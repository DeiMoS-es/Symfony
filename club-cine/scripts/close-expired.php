<?php

$databaseUrl = getenv('DATABASE_URL');

$parts = parse_url($databaseUrl);

$pdo = new PDO(
    "mysql:host={$parts['host']};dbname=" . ltrim($parts['path'], '/'),
    $parts['user'],
    $parts['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);

$sql = "
    UPDATE app_group_recommendation
    SET status = 'closed'
    WHERE vote_deadline < NOW()
    AND status = 'open'
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

echo "Filas afectadas: " . $stmt->rowCount() . PHP_EOL;
