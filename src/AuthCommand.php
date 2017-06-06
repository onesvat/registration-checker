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

        $parts = explode(" ", $arguments);

        if (count($parts) != 2) {
            return $this->replyWithMessage(['text' => 'Bad format. Please use /auth [username] [password] to auth. Example: /auth 2010400000 YourFancyPassword']);
        }

        $username = $parts[0];
        $password = $parts[1];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://registration.boun.edu.tr/scripts/stuinflogin.asp");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "user_name={$username}&user_pass={$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        if (stristr($output, "hatakullanici") !== false) {
            return $this->replyWithMessage(['text' => 'Login failed. Please use /auth [username] [password] to auth. Example: /auth 2010400000 YourFancyPassword']);
        }

        $database_str = file_get_contents($_ENV['DATABASE_LOC']);

        if ($database_str)
            $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']), true);
        else
            $database = [];

        $database[$this->update->getMessage()->getFrom()->getId()] = [
            'telegram_id' => $this->update->getMessage()->getFrom()->getId(),
            'username' => $username,
            'password' => $password,
            'last_hash' => null
        ];

        file_put_contents($_ENV['DATABASE_LOC'], json_encode($database));

        $this->replyWithMessage(['text' => 'Registered']);
    }
}