<?php

class InitialState extends State
{
    public function handleRequest(): void
    {
    }

    public function sendData(): void
    {
        (new ChatResponse($this->context->chat->getId()))->initialResponse();
    }
}
