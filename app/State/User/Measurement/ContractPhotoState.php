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

        // Прикрепить ссылку в амо
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);
        $connector->createNote($leadId, 'Фото договора:');
        $connector->createNote($leadId,$_ENV['SERVER_URL'] . "/contracts/$fileName");

        $data = [
            "id" => (int)$leadId,
            "status_id" => WAITING_FOR_PREPAYMENT_STATUS_ID,
        ];

        $connector->updateLeads($data);
        $connector->createTask($leadId, 'Связаться с клиентом по предоплате', strtotime("+1 days"));

        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото договора", true);
    }
}
