<?php

class ChatResponse
{
    const REFRESH_USERS = "Обновить персонал";
    const CANCEL = "Отмена";
    const YES = "Да";
    const NO = "Нет";

    const RENEW_TASKS = "Обновить задачи";

    const SUCCESSFUL_INSTALLATION = "Успешный монтаж";
    const DEFECT_OR_IMPROVEMENTS = "Косяк или доработки";

    const MEASUREMENT_DONE = "Я на замере";
    const MEASUREMENT_RESCHEDULE = "Перенос замера";
    const MEASUREMENT_CANCEL = "Отмена замера";

    const MONEY_NOT_RECEIVED = "Деньги не получены";

    const PAYMENT_METHOD_CASH = "Наличные";
    const PAYMENT_METHOD_SBER = "Перевод Сбер";
    const PAYMENT_METHOD_TRMINAL = "Эквайринг терминал";
    const PAYMENT_METHOD_BANK = "Расчетный счет";

    const JANUARY = 'Январь';
    const FEBRUARY = 'Февраль';
    const MARCH = 'Март';
    const APRIL = 'Апрель';
    const MAY = 'Май';
    const JUNE = 'Июнь';
    const JULY = 'Июль';
    const AUGUST = 'Август';
    const SEPTEMBER = 'Сентябрь';
    const OCTOBER = 'Октябрь';
    const NOVEMBER = 'Ноябрь';
    const DECEMBER = 'Декабрь';

    private $chatId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function showAllTasks($tasksText, $tasksCount)
    {
        $tasksNums = [];
        for ($i = 1; $i <= $tasksCount; $i++) {
            $tasksNums[] = ["text" => (string)$i];
        }

        $params = [
            'parse_mode' => 'markdown',
            'chat_id' => $this->chatId,
            'text' => $tasksText,
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    $tasksNums,
                    [
                        [
                            "text" => self::RENEW_TASKS,
                        ],
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function showAdminActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Что сделать?',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::REFRESH_USERS,
                        ],
                    ],
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function initialResponse()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Вы в главном меню: /list - список заданий, /cancel - вернуться в начало',
            'reply_markup' => json_encode(
                ['hide_keyboard' => true]
            ),
        ];

        $this->sendMessage($params);
    }

    public function sendText($text, $hideKeyboard = true)
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'reply_markup' => json_encode(
                ['hide_keyboard' => $hideKeyboard]
            ),
        ];

        $this->sendMessage($params);
    }

    public function sendTextWithCancel($text)
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function paymentMethodActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Выберите способ оплаты',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::PAYMENT_METHOD_CASH,
                        ],
                        [
                            "text" => self::PAYMENT_METHOD_SBER,
                        ],
                    ],
                    [
                        [
                            "text" => self::PAYMENT_METHOD_TRMINAL,
                        ],
                        [
                            "text" => self::PAYMENT_METHOD_BANK,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function remainsPaymentMethodActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Как будет оплачен остаток?',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::PAYMENT_METHOD_CASH,
                        ],
                        [
                            "text" => self::PAYMENT_METHOD_SBER,
                        ],
                    ],
                    [
                        [
                            "text" => self::PAYMENT_METHOD_TRMINAL,
                        ],
                        [
                            "text" => self::PAYMENT_METHOD_BANK,
                        ],
                    ],
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function installationActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Выберите действие с монтажом',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::SUCCESSFUL_INSTALLATION,
                        ],
                        [
                            "text" => self::DEFECT_OR_IMPROVEMENTS,
                        ],
                    ],
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function measurementActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Выберите действие с замером',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::MEASUREMENT_DONE,
                        ],
                        [
                            "text" => self::MEASUREMENT_RESCHEDULE,
                        ],
                        [
                            "text" => self::MEASUREMENT_CANCEL,
                        ],
                    ],
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function prepaymentActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Введите полученную сумму предоплаты',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function ReceivingMoneyActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Деньги получены?',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::YES,
                        ],
                    ],
                    [
                        [
                            "text" => self::NO,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    public function monthsActions()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => 'Выберите месяц',
            'reply_markup' => json_encode([
                "resize_keyboard" => true,
                "keyboard" => [
                    [
                        [
                            "text" => self::JANUARY,
                        ],
                        [
                            "text" => self::FEBRUARY,
                        ],
                    ],
                    [
                        [
                            "text" => self::MARCH,
                        ],
                        [
                            "text" => self::APRIL,
                        ],
                    ],
                    [
                        [
                            "text" => self::MAY,
                        ],
                        [
                            "text" => self::JUNE,
                        ],
                    ],
                    [
                        [
                            "text" => self::JULY,
                        ],
                        [
                            "text" => self::AUGUST,
                        ],
                    ],
                    [
                        [
                            "text" => self::SEPTEMBER,
                        ],
                        [
                            "text" => self::OCTOBER,
                        ],
                    ],
                    [
                        [
                            "text" => self::NOVEMBER,
                        ],
                        [
                            "text" => self::DECEMBER,
                        ],
                    ],
                    [
                        [
                            "text" => self::CANCEL,
                        ],
                    ],
                ],
            ]),
        ];

        $this->sendMessage($params);
    }

    private function sendMessage($params)
    {

        $url = "https://api.telegram.org/bot" . $_ENV['BOT_TOKEN'] . "/sendMessage?" . http_build_query($params);

        (new Logger(SEND_MESSAGE_ERROR_PATH))->info($url);
        (new Logger(ROOT . "logs/userActions/$this->chatId.log"))->info($params['text']);
        file_get_contents($url);
    }
}
