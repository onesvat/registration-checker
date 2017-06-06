<?php

namespace Commands;

use Registration\Database;
use Registration\Explorer;
use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class GradesCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "grades";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $pdo = Database::getPdo();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$this->update->getMessage()->getFrom()->getId()]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return $this->replyWithMessage(['text' => 'Please use /auth first']);
        }

        $explorer = new Explorer($user['username'], $user['password']);

        $grades = $explorer->fetchGrades("2016/2017-2");

        if (count($grades['courses']) == 0) {
            return $this->replyWithMessage(['text' => 'Either you do not have any courses, or your credentials are wrong. Try /auth again']);
        }

        $message = "";

        foreach ($grades['courses'] as $course => $grade) {
            $message .= $course . " " . $grade . "\n";
        }

        $message .= "SPA: " . $grades['spa'] . "\n";
        $message .= "GPA: " . $grades['gpa'];


        return $this->replyWithMessage(['text' => "<pre>" . $message . "</pre>", 'parse_mode' => 'HTML']);
    }
}