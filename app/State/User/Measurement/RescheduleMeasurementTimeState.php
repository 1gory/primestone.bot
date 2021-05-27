<?php

class RescheduleMeasurementTimeState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $this->context->chat->setStepData($message, 'time');



        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText('Отправьте Время в формате 12:15');
    }
}
