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

// Remover comillas si existen
$databaseUrl = trim($databaseUrl, '"\'');

// Parsear URL
$parts = parse_url($databaseUrl);

// Validar componentes requeridos
if (!isset($parts['host']) || !isset($parts['user']) || !isset($parts['pass'])) {
    echo "ERROR: DATABASE_URL tiene un formato inválido.\n";
    echo "Contenido parseado: " . json_encode($parts) . "\n";
    die();
}

// Obtener puerto (por defecto 3306 para MySQL)
$port = isset($parts['port']) ? $parts['port'] : 3306;

// Obtener base de datos del path (remover barra inicial)
$database = ltrim($parts['path'], '/');

try {
    $pdo = new PDO(
        "mysql:host={$parts['host']};port={$port};dbname={$database}",
        $parts['user'],
        $parts['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("ERROR: No se pudo conectar a la base de datos: " . $e->getMessage() . "\n");
}

// Primero verifica si hay registros que cumplan la condición
$checkSql = "
    SELECT id, deadline, status 
    FROM app_group_recommendation
    WHERE deadline < NOW()
    AND status = 'open'
";

$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute();
$records = $checkStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($records)) {
    echo "No hay registros expirados para cerrar.\n";
    
    // Debug: mostrar registros abiertos
    $debugSql = "
        SELECT id, deadline, status, NOW() as ahora
        FROM app_group_recommendation
        WHERE status = 'open'
        LIMIT 5
    ";
    $debugStmt = $pdo->prepare($debugSql);
    $debugStmt->execute();
    $debugRecords = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($debugRecords)) {
        echo "DEBUG - Registros abiertos encontrados:\n";
        foreach ($debugRecords as $record) {
            echo "  ID: {$record['id']}, Deadline: {$record['deadline']}, Ahora: {$record['ahora']}\n";
        }
    } else {
        echo "DEBUG - No hay registros abiertos en la tabla.\n";
    }
} else {
    echo "Registros expirados encontrados: " . count($records) . "\n";
    
    // Ejecutar el UPDATE
    $sql = "
        UPDATE app_group_recommendation
        SET status = 'closed'
        WHERE deadline < NOW()
        AND status = 'open'
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "Filas afectadas: " . $stmt->rowCount() . PHP_EOL;
}
