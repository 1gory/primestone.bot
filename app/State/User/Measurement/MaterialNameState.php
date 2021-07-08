<?php

class MaterialNameState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $data = [
            "id" => (int)$leadId,
            "custom_fields_values" => [
                [
                    "field_id" => MATERIAL_NAME_FIELD_ID,
                    "values" => [
                        [
                            "value" => $message,
                        ],
                    ],
                ],
            ]
        ];

        $connector->updateLeads($data);

        $this->context->chat->setState(DesignPhotoState::class);
        $this->context->transitionTo(new DesignPhotoState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel("Напишите название материала:");
    }
}
