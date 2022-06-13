<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$genders = [
    'm' => 'мужской',
    'f' => 'женский',
    'n' => 'средний'
];

$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

$telegram = new Api('5578497960:AAERCgsXvpUoyDRSfhJ7tO_2WZ2CL3NVXDE');
$result = $telegram->getWebhookUpdates();

$text = $result["message"]["text"];
$chat_id = $result["message"]["chat"]["id"];

if ($text) {
    if ($text == "/start") {
        $reply = "Просто введи слово на немецком";
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply ]);
    } else {
        $reply = '';
        $html = simplexml_load_file('https://dict.leo.org/dictQuery/m-vocab/rude/query.xml?lp=rude&lang=ru&search=' . $text . '&side=both&order=basic&partial=show&sectLenMax=16&n=1&filtered=-1&trigger=');
        if ($html) {
            if ($html->sectionlist->section[0]) {
                foreach ($html->sectionlist->section[0]->entry as $entry) {
                    $n = $entry->side[0]->repr->small->i->m->t;
                    foreach ($entry->side[0]->words->word as $word) {
                        $reply .= "<b>" . $word . "</b>\n\n";

                        if ($word->attributes()->implicit_mf) {
                            $reply .= "Род: " . $genders[$word->attributes()->implicit_mf->__toString()] . "\n\n";
                        } else {
                            $reply .= "Грамматическое число: " . $n . "\n\n";
                        }
                    }
                }
            } else {
                $reply .= 'Булочка, ты опечаталась';
            }

            $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $reply ]);
        }
    }
}