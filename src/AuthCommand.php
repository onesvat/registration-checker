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

        list($username, $password) = explode(" ", $arguments);

        $database_str = file_get_contents($_ENV['DATABASE_LOC']);

        if ($database_str)
            $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']));
        else
            $database = [];

        $database[$username] = [
            'username' => $username,
            'password' => $password,
            'last_hash' => null
        ];

        file_put_contents($_ENV['DATABASE_LOC'], json_encode($database));

        $this->replyWithMessage(['text' => 'Registered']);
    }
}