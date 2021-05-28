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
        $connector->createNote($leadId, 'Фото остатка денег:');
        $connector->createNote($leadId,$_ENV['SERVER_URL'] . "/uploads/money/$fileName");

        // todo обновить данные по сделке (тип остатка оплаты)

        $data = [
            "id" => (int)$leadId,
            "status_id" => QUALITY_CONTROL_STATUS_ID,
        ];

        $connector->updateLeads($data);

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото денег (чека)", true);
    }
}
