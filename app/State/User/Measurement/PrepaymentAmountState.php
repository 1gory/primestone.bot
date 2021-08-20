<?php

class PrepaymentAmountState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $prepayment = $message;

        $remains = $data['orderPrice'] - $prepayment;

        $data = [
            "id" => (int)$leadId,
            "custom_fields_values" => [
                [
                    "field_id" => PREPAYMENT_AMOUNT_FIELD_ID,
                    "values" => [
                        [
                            "value" => (int)$prepayment,
                        ],
                    ],
                ],
                [
                    "field_id" => REMAINS_PAYMENT_AMOUNT_FIELD_ID,
                    "values" => [
                        [
                            "value" => (int)$remains,
                        ],
                    ],
                ],
            ]
        ];

        $connector->updateLeads($data);

        $this->context->chat->setState(PaymentMethodState::class);
        $this->context->transitionTo(new PaymentMethodState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->prepaymentActions();
    }
}
