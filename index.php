<?php
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('5578497960:AAERCgsXvpUoyDRSfhJ7tO_2WZ2CL3NVXDE'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя

$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $text ]);

if ($text) {
    if ($text == "/start") {
        $reply = "Просто введи слово на немецком";
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply ]);
    } else {
        $html = simplexml_load_file('https://dict.leo.org/dictQuery/m-vocab/rude/query.xml?lp=rude&lang=ru&search=' . $text . '&side=both&order=basic&partial=show&sectLenMax=16&n=1&filtered=-1&trigger=');
        $reply = $html->sectionlist->section[0]->entry[0]->side[0]->repr->small->i->m->t;
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
    }
} else {
    $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение." ]);
}