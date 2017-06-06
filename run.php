<?php

use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$pdo = \Registration\Database::getPdo();
$telegram = new Api($_ENV['TELEGRAM_KEY']);

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

foreach ($users as $user) {

    $explorer = new \Registration\Explorer($user['username'], $user['password']);
    $grades = $explorer->fetchGrades("2016/2017-2");

    if ($grades === false) {
        continue;
    }

    $hash = md5(json_encode($grades));

    if ($user['last_hash'] == null) {
        $pdo->exec("UPDATE users SET last_hash = '$hash' WHERE id = {$user['id']}");
    } else {
        if ($hash != $user['last_hash']) {

            $message = "";

            foreach ($grades['courses'] as $course => $grade) {
                $message .= $course . " " . $grade . "\n";
            }

            $message .= "SPA: " . $grades['spa'] . "\n";
            $message .= "GPA: " . $grades['gpa'];

            $telegram->sendMessage(['chat_id' => $user['telegram_id'], 'text' => "<b>Grade Changed!!!</b>\n" . "<pre>" . $message . "</pre>", 'parse_mode' => 'HTML']);

            $pdo->exec("UPDATE users SET last_hash = '$hash' WHERE id = {$user['id']}");
        }
    }

    echo $user['username'] . PHP_EOL;

    sleep(mt_rand(1, 2));
}