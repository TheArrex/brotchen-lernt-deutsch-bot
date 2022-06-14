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

$telegram = new Api($_ENV['BOT_TOKEN']);
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
            $reply = '';

            if ($section) {
                $i = 1;
                foreach ($section->entry as $entry) {
                    switch ($entry->info->category->attributes()->type->__toString()) {
                        case 'noun':
                            foreach ($entry->side[0]->words->word as $word) {
                                $reply .= "<b>" . $word . "</b>\n";

                                if ($word->attributes()->implicit_mf) {
                                    $reply .= "род: " . $genders[$word->attributes()->implicit_mf->__toString()] . "\n\n";
                                } else {
                                    $reply .= "грамматическое число: " . $entry->side[0]->repr->small->i->m->t . "\n\n";
                                }
                            }
                            break;
                        case 'verb':
                            $length = count($entry->side[0]->words->word);
                            $x = 1;

                            foreach ($entry->side[0]->words->word as $key => $word) {
                                $reply .= "<b>" . $word . "</b>";
                                if ($x !== $length) {
                                    $reply .= " / ";
                                }
                                $x++;
                            }
                            $reply .= "\n";
                            break;
                    }
                }

                switch ($section->entry[0]->info->category->attributes()->type->__toString()) {
                    case 'noun':
                        $reply .= "<b>мн.ч.: " . $section->entry[0]->side[1]->repr->flecttabref->small . "</b>";
                        break;
                    case 'verb':
                        $stemming = simplexml_load_file('https://dict.leo.org/dictQuery/m-vocab/rude/stemming.xml' . $section->entry[0]->side[1]->ibox->flecttab->attributes()->url . '&onlyLoc=result');

                        if ($stemming) {
                            $reply .= "\n";
                            foreach ($stemming->verbtab->mood[0]->tense[0]->case as $case) {
                                $reply .= $case->verb->fix[0] . $case->verb->var . "\n";
                            }
                        }
                        break;
                    default:
                        $reply .= 'Я пока знаю только существительные и глаголы.';
                        break;
                }
            } else {
                $reply = 'Булочка, ты опечаталась';
            }

            $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $reply ]);

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