<?php

namespace Commands;

use Registration\Database;
use Registration\Explorer;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class AuthCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "auth";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $parts = explode(" ", $arguments);

        if (count($parts) != 2) {
            return $this->replyWithMessage(['text' => 'Bad format. Please use /auth [username] [password] to auth. Example: /auth 2010400000 YourFancyPassword']);
        }

        $username = $parts[0];
        $password = $parts[1];

        $explorer = new Explorer($username, $password);
        $status = $explorer->login();

        if ($status) {
            $pdo = Database::getPdo();

            $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
            $stmt->execute([$username]);

            $stmt = $pdo->prepare("INSERT INTO users (telegram_id, username, password, updated_at, created_at) (?, ?, ?, ?, ?)");
            $stmt->execute([
                $this->update->getMessage()->getFrom()->getId(),
                $username,
                $password,
                null,
                date("Y-m-d H:i:s")
            ]);

            $this->replyWithMessage(['text' => 'Registered :)']);
        } else {
            $this->replyWithMessage(['text' => 'Your credentials were invalid.']);
        }
    }
}