<?php

require_once './config/config.php';
require_once './config/amocrm_settings.php';

if ($_ENV['DEBUG_MODE'] === "true") {
    $data = json_decode(file_get_contents("https://api.telegram.org/bot" . $_ENV['BOT_TOKEN'] . "/getUpdates"), true);
    if (empty($data['result'])) die;
    $data = end($data['result']);
} else {
    $data = json_decode(file_get_contents('php://input'), true);
}

$userName = $data['message']['chat']['username'] ?? null;

User::checkUserAccess($userName);

$chatId = $data['message']['chat']['id'];
$chatType = $data['message']['chat']['type'];
$messageText = $data['message']['text'] ?? null;
$messagePhoto = $data['message']['photo'] ?? null;

(new Logger(ROOT . "logs/userActions/$chatId.log"))->info($messageText);

// в объекте chat храним данные диалога с пользователем
$Chat = new Chat($chatId);

if ($chatType !== 'private') {
    die;
}

$stateName = $Chat->getState();
// текущее шаг пользователя
$State = class_exists($stateName) ? new $stateName : $State = new InitialState();

// Контроллер
$Context = new Context($State, $Chat, $userName, $messageText, $messagePhoto);

try {
    $Context->handleRequest();
    $Context->sendResponse();
} catch (Exception $e) {
    (new Logger(ERROR_PATH))->error($e->getMessage());
}
