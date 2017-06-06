<?php

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
        $this->replyWithMessage(['text' => 'Hello ' . $this->update->getMessage()->getFrom()->getUsername() . '! Please use /auth $USERNAME:$PASSWORD to auth']);
    }
}