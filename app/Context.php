<?php

class Context
{
    /**
     * @var string
     */
    public $userName;

    /**
     * @var string
     */
    public $messageText;

    /**
     * @var array
     */
    public $messagePhoto;

    /**
     * @var Chat
     */
    public $chat;

    /**
     * @var State
     */
    private $state;

    public function __construct(State $state, Chat $chat, $userName, $messageText, $messagePhoto)
    {
        $this->chat = $chat;
        $this->userName = $userName;
        $this->messageText = $messageText;
        $this->messagePhoto = $messagePhoto;
        $this->transitionTo($state);
    }

    public function transitionTo(State $state): void
    {
        $this->state = $state;
        $this->state->setContext($this);
    }

    public function handleRequest(): void
    {
        switch ($this->messageText) {
            case '/admin':
                User::checkAdminAccess($this->userName);
                $this->chat->flushData();
                $this->chat->setState(AdminState::class);
                $this->transitionTo(new AdminState());
                break;
            case '/list':
                $this->chat->flushData();
                $this->chat->setState(UserState::class);
                $this->transitionTo(new UserState());
                break;
            case ChatResponse::CANCEL:
            case '/cancel':
                $this->chat->flushData();
                $this->chat->setState(InitialState::class);
                $this->transitionTo(new InitialState());
                break;
            default:
                $this->state->handleRequest();
        }
    }

    public function sendResponse(): void
    {
        if ($this->state->hasError()) {
            (new ChatResponse($this->chat->getId()))->sendText($this->state->getErrorMessage());
        }

        if ($this->state->omitResponse()) {
            return;
        }

        $this->state->sendData();
    }
}
