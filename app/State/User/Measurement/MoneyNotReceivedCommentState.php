<?php

class MoneyNotReceivedCommentState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $data = [
            "id" => (int)$leadId,
            "status_id" => NO_MONEY_OR_NO_CONTRACT_STATUS_ID,
        ];

        $connector->createNote($leadId, $message);

        $connector->updateLeads($data);
        $connector->createTask($leadId, 'Связаться с клиентом', strtotime("+1 days"));

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel('Коментарий (Укажите причину)');
    }
}
