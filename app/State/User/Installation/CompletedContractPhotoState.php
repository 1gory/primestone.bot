<?php

class CompletedContractPhotoState extends State
{
    public function handleRequest(): void
    {
//        $this->context->chat->setStepData($message, 'taskNumber');

        $this->context->chat->setState(RemainsPaymentMethodState::class);
        $this->context->transitionTo(new RemainsPaymentMethodState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Прикрепите фото подписанного акта выполненных работ", false);
    }
}
