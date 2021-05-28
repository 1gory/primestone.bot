<?php

class
RescheduleMeasurementTimeState extends State
{
    public function handleRequest(): void
    {
        $time = $this->context->messageText;

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $day = $data['day'];
        $month = $data['month'];

        $dateTimeStart = DateTime::createFromFormat('m d H:i', "$month $day $time");

        if (!$dateTimeStart) {
            $ChatResponse = new ChatResponse($this->context->chat->getId());
            $ChatResponse->sendText('Неверно введена дата или вермя, попробуйте еще раз');

            $this->context->chat->setState(RescheduleMeasurementDateState::class);
            $this->context->transitionTo(new RescheduleMeasurementDateState());
            return;
        }

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $data = [
            "id" => (int)$leadId,
            "custom_fields_values" => [
                [
                    "field_id" => MEASUREMENT_DATETIME_FIELD_ID,
                    "values" => [
                        [
                            "value" => (int)$dateTimeStart->format('U'),
                        ],
                    ],
                ],
            ],
        ];

        $connector->updateLeads($data);

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача перенесена, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText('Отправьте Время в формате 12:15');
    }
}
