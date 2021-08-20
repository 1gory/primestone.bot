<?php

class MeasurementState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        switch ($message) {
            case ChatResponse::MEASUREMENT_DONE:
                $this->context->chat->setState(ReceivingMoneyState::class);
                $this->context->transitionTo(new ReceivingMoneyState());
                break;
            case ChatResponse::MEASUREMENT_RESCHEDULE:
                $this->context->chat->setState(RescheduleMeasurementMonthState::class);
                $this->context->transitionTo(new RescheduleMeasurementMonthState());
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
