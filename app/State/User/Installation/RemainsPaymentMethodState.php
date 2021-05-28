<?php

class RemainsPaymentMethodState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $data = [
            "id" => (int)$leadId,
            "custom_fields_values" => [
                [
                    "field_id" => REMAINS_PAYMENT_TYPE_FIELD_ID,
                    "values" => [
                        [
                            "value" => $message,
                        ],
                    ],
                ],
            ]
        ];

        $connector->updateLeads($data);

        switch ($message) {
            case ChatResponse::PAYMENT_METHOD_CASH:
            case ChatResponse::PAYMENT_METHOD_SBER:
            case ChatResponse::PAYMENT_METHOD_TRMINAL:
                $this->context->chat->setState(RemainsMoneyPhotoState::class);
                $this->context->transitionTo(new RemainsMoneyPhotoState());
                break;
            case ChatResponse::PAYMENT_METHOD_BANK:
                $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
                $data = $this->context->chat->getStepData();
                $index = $data['taskNumber'] - 1;
                $leadId = $data['leadsIds'][$index];

                $data = [
                    "id" => (int)$leadId,
                    "status_id" => WAITING_FOR_FULL_PAYMENT_STATUS_ID,
                ];

                $connector->updateLeads($data);
                $connector->createTask($leadId, 'Связаться с клиентом', strtotime("+1 days"));

                $ChatResponse = new ChatResponse($this->context->chat->getId());
                $ChatResponse->sendText("Задача закрыта, данные обновлены");

                $this->context->chat->flushData();
                $this->context->chat->setState(InitialState::class);
                $this->context->transitionTo(new InitialState());

                break;
            default:
                $this->setError('Неправильный ввод');
                return;
        }

    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->remainsPaymentMethodActions();
    }
}
