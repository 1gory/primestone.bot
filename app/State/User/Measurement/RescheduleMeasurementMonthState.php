<?php

class RescheduleMeasurementMonthState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $this->context->chat->setStepData($message, 'month');

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->monthsActions();
    }
}
