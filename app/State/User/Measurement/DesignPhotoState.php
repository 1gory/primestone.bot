<?php

class DesignPhotoState extends State
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
        $fileName = FileUploader::downloadFile($leadId, 'designs', $photos);

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $text = "Фото чертежа: \r\n" . $_ENV['SERVER_URL'] . "/uploads/designs/$fileName";
        $connector->createNote($leadId, $text);

        $this->context->chat->setState(OrderPriceState::class);
        $this->context->transitionTo(new OrderPriceState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Прикрепите фото чертежа");
    }
}
