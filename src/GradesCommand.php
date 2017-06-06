<?php

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

        $database_str = file_get_contents($_ENV['DATABASE_LOC']);

        if ($database_str)
            $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']), true);
        else
            $database = [];

        if (!array_key_exists($this->update->getMessage()->getFrom()->getId(), $database)) {
            return $this->replyWithMessage(['text' => 'Please register first']);
        }

        $user = $database[$this->update->getMessage()->getFrom()->getId()];

        $cookie_location = tempnam("/tmp", "registration-");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://registration.boun.edu.tr/scripts/stuinflogin.asp");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "user_name={$user['username']}&user_pass={$user['password']}");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_location);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_location);

        curl_exec($ch);
        curl_close($ch);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://registration.boun.edu.tr/scripts/stuinfgs.asp?donem=2016/2017-2");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_location);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_location);

        $output = curl_exec($ch);
        curl_close($ch);

        return $this->replyWithMessage(['text' => $output]);
    }
}