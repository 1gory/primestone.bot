<?php

class CancelMeasurementState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача отменена, данные обновлены");

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $data = [
            "id" => (int)$leadId,
            "status_id" => CANCELING_THE_MEASUREMENT_STATUS_ID,
        ];

        $connector->createNote($leadId, $message);
        $connector->updateLeads($data);
        $connector->createTask($leadId, 'Связаться с клиентом по отмене заказа', strtotime("+1 days"));

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel('Укажите причину отказа');
    }
}
