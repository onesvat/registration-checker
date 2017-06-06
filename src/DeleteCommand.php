<?php

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

        $database_str = file_get_contents($_ENV['DATABASE_LOC']);

        if ($database_str)
            $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']), true);
        else
            $database = [];

        unset($database[$this->update->getMessage()->getFrom()->getId()]);

        file_put_contents($_ENV['DATABASE_LOC'], json_encode($database));

        $this->replyWithMessage(['text' => 'Deleted']);
    }
}