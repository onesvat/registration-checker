<?php

use Commands\AuthCommand;
use Commands\BuCardCommand;
use Commands\DeleteCommand;
use Commands\GradesCommand;
use Commands\StartCommand;
use Telegram\Bot\Api;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$telegram = new Api($_ENV['TELEGRAM_KEY']);

$telegram->addCommands([
    StartCommand::class,
    AuthCommand::class,
    GradesCommand::class,
    BuCardCommand::class,
    DeleteCommand::class
]);

$telegram->commandsHandler(true);

