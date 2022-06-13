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
        $html = simplexml_load_file('https://dict.leo.org/dictQuery/m-vocab/rude/query.xml?lp=rude&lang=ru&search=' . $text . '&side=both&order=basic&partial=show&sectLenMax=16&n=1&filtered=-1&trigger=');
        if ($html) {
            $section = $html->sectionlist->section[0];
            if ($section) {
                $i = 1;
                foreach ($section->entry as $entry) {
                    if ($i++ > 3) break;
                    $reply = '';
                    $entryAttributes = $entry->side[0]->ibox->flecttab->attributes();

                    $n = $entry->side[0]->repr->small->i->m->t;
                    foreach ($entry->side[0]->words->word as $word) {
                        $reply .= "<b>" . $word . "</b>\n\n";

                        switch ($entryAttributes->stemType->__toString()) {
                            case 'noun':
                                if ($word->attributes()->implicit_mf) {
                                    $reply .= "Род: " . $genders[$word->attributes()->implicit_mf->__toString()] . "\n\n";
                                } else {
                                    $reply .= "Грамматическое число: " . $n . "\n\n";
                                }
                                break;
                            case 'verb':
                                $reply .= getIndikativ($entryAttributes->url->__toString());
                                break;
                        }
                    }

                    $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $reply ]);
                }
            } else {
                $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => 'Булочка, ты опечаталась' ]);
            }

            if ($section) {
                $fileName = $section->entry[1]->side[1]->ibox->pron->attributes()->url;
                $telegram->sendVoice([
                    'chat_id' => $chat_id,
                    'voice' => 'https://dict.leo.org/media/audio/' . $fileName . '.ogg',
                ]);
            }
        }
    }
}

function getIndikativ($url) {
    $result = '';
    $html = simplexml_load_file('https://dict.leo.org/dictQuery/m-vocab/rude/stemming.xml' . $url . '&onlyLoc=result');

    if ($html) {
        foreach ($html->flectiontable->verbtab->mood[0]->tense[0]->case as $case) {
            $result = $case->verb->__toString();
        }
    }

    return $result;
}