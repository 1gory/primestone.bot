<?php

class InstallationState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        switch ($message) {
            case ChatResponse::SUCCESSFUL_INSTALLATION:
                $this->context->chat->setState(TabletopPhotoState::class);
                $this->context->transitionTo(new TabletopPhotoState());
                break;
            case ChatResponse::DEFECT_OR_IMPROVEMENTS:
                $this->context->chat->setState(DefectCommentState::class);
                $this->context->transitionTo(new DefectCommentState());
                break;
            default:
                $this->setError('Неправильный ввод');
        }
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->installationActions();
    }
}
