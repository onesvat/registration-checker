<?php

use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$cookie_location = tempnam("/tmp", "registration-");
$last_hash_location = $_ENV['LOC_HASH'];
$user_name = $_ENV['USERNAME'];
$user_pass = $_ENV['PASSWORD'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://registration.boun.edu.tr/scripts/stuinflogin.asp");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
    "user_name=$user_name&user_pass=$user_pass");

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

if (md5($output) != file_get_contents($last_hash_location)) {
    file_put_contents($last_hash_location, md5($output));

    return new Api($_ENV['TELEGRAM_API_KEY']);
}


echo $output;