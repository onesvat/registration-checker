<?php

use Sunra\PhpSimple\HtmlDomParser;
use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$database_str = file_get_contents($_ENV['DATABASE_LOC']);

if ($database_str)
    $database = json_decode(file_get_contents($_ENV['DATABASE_LOC']), true);
else
    $database = [];

foreach ($database as $user) {

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

    if (md5($output) != $user['last_hash']) {
        $dom = HtmlDomParser::str_get_html($output);

        $message = "";

        foreach ($dom->find('table', 1)->find("tr[class=recmenu]") as $element) {
            $course = str_replace("&nbsp;", "", $element->find("td", 0)->plaintext);
            $grade = str_replace("&nbsp;", "", $element->find("td", 3)->plaintext);

            if ($grade == "") {
                $grade = "NA";
            }

            $message .= $course . " " . $grade . "\n";
        }

        $telegram = new Api($_ENV['TELEGRAM_KEY']);
        $telegram->sendMessage(['chat_id' => $user['telegram_id'], 'text' => "<b>Grade Changed!!!</b>\n" . "<pre>" . $message . "</pre>", 'parse_mode' => 'HTML']);
    }
}

file_put_contents($_ENV['DATABASE_LOC'], json_encode($database));