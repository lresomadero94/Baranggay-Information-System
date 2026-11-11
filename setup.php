<?php
require_once __DIR__ . '/config.php';
try {
    $count = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = db()->prepare('INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)');
        $stmt->execute(['System Administrator', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
        echo 'Initial administrator created. Username: admin | Password: admin123. Delete setup.php now, then sign in at index.php.';
    } else {
        echo 'An administrator account already exists. Delete setup.php now.';
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Setup failed. Import database.sql and check config.php. Details: ' . htmlspecialchars($e->getMessage());
}
