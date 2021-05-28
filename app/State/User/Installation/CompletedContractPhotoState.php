<?php

class CompletedContractPhotoState extends State
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
        $fileName = FileUploader::downloadFile($leadId, 'acts', $photos);

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $connector->createNote($leadId, 'Фото акта выполненных работ:');
        $connector->createNote($leadId,$_ENV['SERVER_URL'] . "/uploads/acts/$fileName");

        $this->context->chat->setState(RemainsPaymentMethodState::class);
        $this->context->transitionTo(new RemainsPaymentMethodState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото подписанного акта выполненных работ", false);
    }
}
