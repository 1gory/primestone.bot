<?php

class UserState extends State
{
    public function handleRequest(): void
    {
        $message = $this->context->messageText;

        $this->context->chat->setStepData($message, 'taskNumber');

        $data = $this->context->chat->getStepData();
        $taskIndex = $message - 1;
        $type = $data['leadsType'][$taskIndex];

        if ($type === "measurement") {
            $this->context->chat->setState(MeasurementState::class);
            $this->context->transitionTo(new MeasurementState());
        } else if ($type === "installation") {
            $this->context->chat->setState(InstallationState::class);
            $this->context->transitionTo(new InstallationState());
        }
    }

    public function sendData(): void
    {
        $ChatResponse = new ChatResponse($this->context->chat->getId());
        $userName = User::getUserNameByLogin($this->context->userName);
        $data = AmoCrmData::getTasksData($userName);

        if (!count($data['leadsIds'])) {
            $ChatResponse->sendText('Задачи на сегодня и завтра отсутсвтуют', true);
        } else {
            $this->context->chat->setStepData($data['leadsIds'], 'leadsIds');
            $this->context->chat->setStepData($data['leadsType'], 'leadsType');
            $this->context->chat->setStepData($data['googleEventsIds'], 'googleEventsIds');

            $ChatResponse->showAllTasks($data['text'], count($data['leadsIds']));
            $ChatResponse->sendText('Выберите номер задачи', false);
        }
    }
}
