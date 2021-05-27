<?php

class MeasurementState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        switch ($message) {
            case ChatResponse::MEASUREMENT_DONE:
                $this->context->chat->setState(DesignPhotoState::class);
                $this->context->transitionTo(new DesignPhotoState());
                break;
            case ChatResponse::MEASUREMENT_RESCHEDULE:
                $this->context->chat->setState(RescheduleMeasurementDateState::class);
                $this->context->transitionTo(new RescheduleMeasurementDateState());
                break;
            case ChatResponse::MEASUREMENT_CANCEL:
                $this->context->chat->setState(CancelMeasurementState::class);
                $this->context->transitionTo(new CancelMeasurementState());
                break;
            default:
                $this->setError('Неправильный ввод');
        }
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->measurementActions();
    }
}
