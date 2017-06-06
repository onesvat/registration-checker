<?php

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);
        $this->replyWithMessage(['text' => 'Hello ' . $this->update->getMessage()->getFrom()->getUsername() . '! Please use /auth $USERNAME $PASSWORD to auth']);
    }
}