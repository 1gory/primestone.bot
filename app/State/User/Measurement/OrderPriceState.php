<?php

class OrderPriceState extends State
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
            "price" => (int)$message
        ];

        $this->context->chat->setStepData($message, 'orderPrice');
        $connector->updateLeads($data);

        $this->context->chat->setState(PrepaymentAmountState::class);
        $this->context->transitionTo(new PrepaymentAmountState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel('Введите расчитанную сумму (смету) заказа');
    }
}
