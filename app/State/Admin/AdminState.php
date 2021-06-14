<?php

class AdminState extends State
{
    public function handleRequest(): void
    {
        switch ($this->context->messageText) {
            case ChatResponse::REFRESH_USERS:
                $response = new ChatResponse($this->context->chat->getId());
                $response->sendText("Подождите несколько секунд...");
                // забрать данные из таблицы
                $googleSheetData = GoogleSheet::getSheetData();

                // обновить поля в Амо
                $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
                $fields = $connector->getFieldsInfo();

                foreach ($fields['_embedded']['custom_fields'] as $field) {
                    if (!($field['id'] === AMO_MEASURER_FIELD_ID || $field['id'] === AMO_INSTALLER_FIELD_ID)) {
                        continue;
                    }

                    // $field - либо список мотнажников, либо список замерщиков
                    $updatedField = [
                        'name' => $field['name'],
                        'enums' => [],
                    ];

                    foreach ($googleSheetData as $row) {
                        $name = $row[0];

                        foreach ($field['enums'] as $amoFieldValue) {
                            if ($amoFieldValue['value'] === $name) {
                                $updatedField['enums'][] = $amoFieldValue;
                                continue(2);
                            }
                        }

                        $updatedField['enums'][] = [
                            'value' => $name,
                        ];
                    }

                    $connector->updateField($field['id'], $updatedField);
                }

                // Обновить пользователей в телеграм-боте
                $updatedUsersList = [];
                $updatedUsersNames = [];
                foreach ($googleSheetData as $row) {
                    $name = $row[0];
                    $telegramLogin = $row[1];
                    $updatedUsersList[] = $telegramLogin . ' ' . $name;
                    $updatedUsersNames[] = $name;
                }

                User::addUsersArray($updatedUsersList);

                // обновить календари
                GoogleCalendarData::updateCalendars($updatedUsersNames);

                $users = implode("\r\n", $updatedUsersList);
                $response->sendText("Персонал обновлен:\r\n$users");

                $this->context->chat->setState(InitialState::class);
                $this->context->transitionTo(new InitialState());

                return;

            case ChatResponse::CANCEL:
                $this->context->chat->setState(InitialState::class);
                $this->context->transitionTo(new InitialState());
                break;
            default:
                $this->setError('Неправильный ввод');
        }
    }

    public function sendData(): void
    {
        $this->context->chat->flushData();
        (new ChatResponse($this->context->chat->getId()))->showAdminActions();
    }
}
