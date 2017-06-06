<?php

use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$telegram = new Api($_ENV['TELEGRAM_KEY']);
$telegram->setWebhook(['url' => $_ENV['TELEGRAM_WEBHOOK']]);