<?php

namespace Commands;

use Registration\Database;
use Registration\Explorer;
use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class BuCardCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "bucard";

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

        $cards = $explorer->fetchBuCardDetails();

        if (count($cards) == 0) {
            return $this->replyWithMessage(['text' => 'Either you do not have any bucard, or your credentials are wrong. Try /auth again']);
        }

        $message = "";

        foreach ($cards as $number => $balance) {
            $message .= $number . " " . $balance . "\n";
        }

        return $this->replyWithMessage(['text' => "<pre>" . $message . "</pre>", 'parse_mode' => 'HTML']);
    }
}