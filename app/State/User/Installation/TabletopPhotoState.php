<?php

class TabletopPhotoState extends State
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
        $fileName = FileUploader::downloadFile($leadId, 'tabletops', $photos);

        // Прикрепить ссылку в амо
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $text = "Фото столешницы: \r\n" . $_ENV['SERVER_URL'] . "/uploads/tabletops/$fileName";
        $connector->createNote($leadId, $text);

        $this->context->chat->setState(CompletedContractPhotoState::class);
        $this->context->transitionTo(new CompletedContractPhotoState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Прикрепите фото готовой столешницы");
    }
}
