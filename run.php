<?php

use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Api;
use Commands\GradesCommand;

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
    $term = GradesCommand::getCurrentTerm();
    $grades = $explorer->fetchGrades($term);

    if ($grades === false) {
        continue;
    }

    $hash = md5(json_encode($grades));
    $grades_json = json_encode($grades['courses']);

    $stmt = $pdo->prepare("UPDATE users SET updated_at = ? WHERE telegram_id = ?");
    $stmt->execute([date("Y-m-d H:i:s"), $user['telegram_id']]);

    if ($user['last_term'] != $term) {
        $stmt = $pdo->prepare("UPDATE users SET last_term = ?, last_hash = null, last_grades = null WHERE telegram_id = ?");
        $stmt->execute([$term, $user['telegram_id']]);

        try {
            $telegram->sendMessage(['chat_id' => $user['telegram_id'], 'text' => "Welcome to new term: <b>" . $term . "</b>\n" . "Grade changes will be detected automatically.", 'parse_mode' => 'HTML']);
        } catch (\Exception $e) {
            continue;
        }

        continue;
    }

    if ($user['last_hash'] == null || $user['last_grades'] == null) {
        $pdo->exec("UPDATE users SET last_hash = '$hash', last_grades = '$grades_json' WHERE id = {$user['id']}");
    } else {
        if ($hash != $user['last_hash']) {

            $pdo->exec("UPDATE users SET last_hash = '$hash', last_grades = '$grades_json' WHERE id = {$user['id']}");

            $message = "";

            $last_grades = json_decode($user['last_grades'], true);

            foreach ($grades['courses'] as $course => $grade) {
                if (array_key_exists($course, $last_grades) && $last_grades[$course] != $grade) {
                    $message .= $course . " <b>" . $grade . "</b>\n";
                }
            }

            try {
                $telegram->sendMessage(['chat_id' => $user['telegram_id'], 'text' => "<b>Grade Changed!!!</b>\n\n" . "<pre>" . $message . "</pre>To check all grades please send /grades command", 'parse_mode' => 'HTML']);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
