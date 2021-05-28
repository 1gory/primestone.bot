<?php

class RescheduleMeasurementMonthState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $months = [
            'Январь' => '01',
            'Февраль' => '02',
            'Март' => '03',
            'Апрель' => '04',
            'Май' => '05',
            'Июнь' => '06',
            'Июль' => '07',
            'Август' => '08',
            'Сентябрь' => '09',
            'Октябрь' => '10',
            'Ноябрь' => '11',
            'Декабрь' => '12',
        ];

        if (empty($months[$message])) {
            $this->setError('Неправильный ввод');
            return;
        }

        $this->context->chat->setStepData($months[$message], 'month');

        $this->context->chat->setState(RescheduleMeasurementDateState::class);
        $this->context->transitionTo(new RescheduleMeasurementDateState());
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $ChatResponse->monthsActions();
    }
}
