<?php

class BotLogger {
    public static function logIt($message)
    {
        if (is_array($message)) {
            $message = join("\r\n", $message);
        }

        $message = BOT_NAME . "\r\n" . $message;

        $params = [
            'chat_id' => 5644754,
            'text' => $message,
        ];
        $url = "https://api.telegram.org/bot1133114437:AAGn4Ve87aq9mFBdlWAhyKxFRnQKNrenaAw/sendMessage?" . http_build_query($params);
        file_get_contents($url);
    }
}
