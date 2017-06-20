<?php

namespace Commands;

use Registration\Database;
use Registration\Explorer;
use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ScheduleCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "schedule";

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

        $courses = $explorer->fetchSchedule();

        if (count($courses) == 0) {
            return $this->replyWithMessage(['text' => 'Either you did not enrolled any course, or your credentials are wrong. Try /auth again']);
        }

        $message = "";

        foreach ($courses as $key => $course) {
            $message .= $course['name'] . "\n";

            foreach ($course['schedule'] as $item) {
                $message .= " " . $item['day_text'] . " " . $item['start_text'] . "-" . $item['end_text'] . " @" . $item['location'] . "\n";
            }
        }

        return $this->replyWithMessage(['text' => "<pre>" . $message . "</pre>", 'parse_mode' => 'HTML']);
    }
}