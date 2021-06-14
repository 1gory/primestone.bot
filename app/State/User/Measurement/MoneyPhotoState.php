<?php

class MoneyPhotoState extends State
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

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $text = "Фото денег (чека):\r\n" . $_ENV['SERVER_URL'] . "/uploads/money/$fileName";
        $connector->createNote($leadId, $text);

        $data = [
            "id" => (int)$leadId,
            "status_id" => ORDERING_MATERIALS_STATUS_ID,
        ];

        $connector->updateLeads($data);

        $this->context->chat->setState(ContractPhotoState::class);
        $this->context->transitionTo(new ContractPhotoState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото денег (чека)", true);
    }
}
