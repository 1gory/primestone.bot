<?php

class DefectCommentState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $connector->createNote($leadId, "Комментарий к косяку или доработке: $message");

        // todo задача на производство проконтролировать

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $data = [
            "id" => (int)$leadId,
            "status_id" => DEFECT_OR_REDO_STATUS_ID,
        ];

        $connector->updateLeads($data);
        $connector->createTask($leadId, 'Проконтролировать косяк или доработку', strtotime("+1 days"));

        $ChatResponse->sendText("Задача обновлена");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Напишите комментарий");
    }
}
