<?php

use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

require  __DIR__ . "/src/StartCommand.php";
require  __DIR__ . "/src/AuthCommand.php";


$telegram = new Api($_ENV['TELEGRAM_KEY']);

$telegram->addCommands([
    StartCommand::class,
    AuthCommand::class
]);

$telegram->commandsHandler(true);

