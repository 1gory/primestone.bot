<?php

class RemainsMoneyPhotoState extends State
{
    public function handleRequest(): void
    {
        $photos = $this->context->messagePhoto;

        if (empty($photos)) {
            $this->setError('Отправьте фото');
            return;
        }

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];
        $fileName = FileUploader::downloadFile($leadId, 'money', $photos);

        // Прикрепить ссылку в амо
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $text = "Фото остатка денег: \r\n" . $_ENV['SERVER_URL'] . "/uploads/money/$fileName";
        $connector->createNote($leadId, $text);

        $data = [
            "id" => (int)$leadId,
            "status_id" => QUALITY_CONTROL_STATUS_ID,
        ];

        $connector->updateLeads($data);

        $text = 'Проконтролировать прием средств';
        $connector->createTask($leadId, $text, strtotime("+1 days"), MANAGER_ID);

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Прикрепите фото денег (чека)");
    }
}
