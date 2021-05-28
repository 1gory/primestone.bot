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

        if ($message === ChatResponse::MONEY_NOT_RECEIVED) {
            $data = [
                "id" => (int)$leadId,
                "status_id" => NO_MONEY_OR_NO_CONTRACT_STATUS_ID,
            ];

            $connector->updateLeads($data);
            $connector->createTask($leadId, 'Связаться с клиентом', strtotime("+1 days"));

            $this->context->chat->setState(MoneyNotReceivedCommentState::class);
            $this->context->transitionTo(new MoneyNotReceivedCommentState());
            return;
        }

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
