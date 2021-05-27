<?php

class PaymentMethodState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $prepaymentMethods = [
            ChatResponse::PAYMENT_METHOD_CASH => CASH_PAYMENT_METHOD,
            ChatResponse::PAYMENT_METHOD_SBER => SBER_PAYMENT_METHOD,
            ChatResponse::PAYMENT_METHOD_BANK => BANK_PAYMENT_METHOD,
            ChatResponse::PAYMENT_METHOD_TRMINAL => TERMINAL_PAYMENT_METHOD,
        ];

        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $data = $this->context->chat->getStepData();
        $index = $data['taskNumber'] - 1;
        $leadId = $data['leadsIds'][$index];

        $data = [
            "id" => (int)$leadId,
            "custom_fields_values" => [
                [
                    "field_id" => PREPAYMENT_TYPE_FIELD_ID,
                    "values" => [
                        [
                            "value" => $prepaymentMethods[$message],
                        ],
                    ],
                ],
            ]
        ];

        $connector->updateLeads($data);

        switch ($message) {
            case ChatResponse::PAYMENT_METHOD_CASH:
            case ChatResponse::PAYMENT_METHOD_SBER:
            case ChatResponse::PAYMENT_METHOD_TRMINAL:
                $this->context->chat->setState(MoneyPhotoState::class);
                $this->context->transitionTo(new MoneyPhotoState());
                break;
            case ChatResponse::PAYMENT_METHOD_BANK:
                $this->context->chat->setState(ContractPhotoState::class);
                $this->context->transitionTo(new ContractPhotoState());
                break;
            default:
                $this->setError('Неправильный ввод');
                return;
        }
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->paymentMethodActions();
    }
}
