<?php

use React\EventLoop\Loop;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;

const BOT_TOKEN = '5578497960:AAERCgsXvpUoyDRSfhJ7tO_2WZ2CL3NVXDE';
const A_USER_CHAT_ID = 241905674;

$loop = Loop::get();
$handler = new HttpClientRequestHandler($loop);
$tgLog = new TgLog(BOT_TOKEN, $handler);

$sendMessage = new SendMessage();
$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = 'Hello world!';

$tgLog->performApiRequest($sendMessage);
$loop->run();