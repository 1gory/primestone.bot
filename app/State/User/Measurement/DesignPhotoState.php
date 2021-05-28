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
        $connector->createNote($leadId, 'Фото чертежа:');
        sleep(1);
        $connector->createNote($leadId,$_ENV['SERVER_URL'] . "/uploads/designs/$fileName");

        $this->context->chat->setState(OrderPriceState::class);
        $this->context->transitionTo(new OrderPriceState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото чертежа", true);
    }
}
