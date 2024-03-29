<?php

class RescheduleMeasurementDateState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $this->context->chat->setStepData($message, 'day');

        $this->context->chat->setState(RescheduleMeasurementTimeState::class);
        $this->context->transitionTo(new RescheduleMeasurementTimeState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel('Отправьте число месяца');
    }
}
