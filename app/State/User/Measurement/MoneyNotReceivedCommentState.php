<?php

class MoneyNotReceivedCommentState extends State
{
    public function handleRequest(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendText("Задача закрыта, данные обновлены");

        $this->context->chat->flushData();
        $this->context->chat->setState(InitialState::class);
        $this->context->transitionTo(new InitialState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->sendTextWithCancel('Коментарий (Укажите причину)');
    }
}
