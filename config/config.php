<?php

date_default_timezone_set ('Europe/Moscow');
ini_set('error_log', __DIR__ . '/../logs/error.log');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

const ADMINS_USERNAME = [
    'igoryp'
];

const ROOT =  __DIR__ . '/../';

const DEBUG_PATH = ROOT . '/logs/debug.log';
const ERROR_PATH = ROOT . '/logs/error.log';

const SEND_MESSAGE_ERROR_PATH = ROOT . '/logs/send_message_error.log';

const DATA_DIR = ROOT . '/data';
const CHAT_DATA_DIR = ROOT . '/data/chats_data';
const UPLOADS_DIR = ROOT . '/uploads';

const CREDENTIALS_DIR = ROOT . '/credentials';

const AMOCRM_TOKENS_PATH = CREDENTIALS_DIR . '/amocrm_tokens.json';

require_once ROOT . '/lib/Autoloader.php';
require_once ROOT . '/lib/Logger.php';
require ROOT . '/vendor/autoload.php';

Autoloader::setFileExt('.php');
Autoloader::setPath(ROOT . '/app');
spl_autoload_register('Autoloader::loader');

// for loading .env variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT);

$dotenv->load();

$dotenv->required('DEBUG_MODE')->isBoolean();
$dotenv->required(['BOT_TOKEN', 'SERVER_URL']);
