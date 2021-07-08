<?php

class ContractPhotoState extends State
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
        $fileName = FileUploader::downloadFile($leadId, 'contracts', $photos);

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        // Прикрепить ссылку в амо
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $text = "Фото договора: \r\n" . $_ENV['SERVER_URL'] . "/uploads/contracts/$fileName";
        $connector->createNote($leadId, $text);

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Прикрепите фото договора");
    }
}
