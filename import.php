<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$database_str = file_get_contents($_ENV['DATABASE_LOC']);

if ($database_str)
    $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']), true);
else
    $database = [];

$pdo = \Registration\Database::getPdo();

foreach ($database as $item) {
    $stmt = $pdo->prepare("INSERT INTO users (telegram_id, username, password, updated_at, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $item['telegram_id'],
        $item['username'],
        $item['password'],
        null,
        (array_key_exists('register_date', $item) ? $item['register_date'] : null)
    ]);
}