<?php
namespace Commands;

use Registration\Database;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class DeleteCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "delete";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $pdo = Database::getPdo();

        $stmt = $pdo->prepare("DELETE FROM users WHERE telegram_id = ?");
        $stmt->execute([$this->update->getMessage()->getFrom()->getId()]);

        $this->replyWithMessage(['text' => 'Deleted :(']);
    }
}