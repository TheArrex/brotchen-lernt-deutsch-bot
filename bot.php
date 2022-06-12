<?php

use React\EventLoop\Loop;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;

$loop = Loop::get();
$handler = new HttpClientRequestHandler($loop);
$tgLog = new TgLog(BOT_TOKEN, $handler);

$sendMessage = new SendMessage();
$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = 'Hello world!';

$tgLog->performApiRequest($sendMessage);
$loop->run();