<?php

class ReceivingMoneyState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        if ($message === ChatResponse::NO) {
            $this->context->chat->setState(MoneyNotReceivedCommentState::class);
            $this->context->transitionTo(new MoneyNotReceivedCommentState());
            return;
        }

        if ($message === ChatResponse::YES) {
            $this->context->chat->setState(MaterialNameState::class);
            $this->context->transitionTo(new MaterialNameState());
            return;
        }

        $this->setError('Неверный ввод');
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->ReceivingMoneyActions();
    }
}
