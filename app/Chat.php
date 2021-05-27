<?php

class Chat
{
    private $id;

    private $filePath;

    private $state;

    private $stepData = [];

    public function __construct($id)
    {
        $this->id = $id;

        $this->filePath = CHAT_DATA_DIR . "/$id" . '.txt';

        if (!file_exists($this->filePath)) {
            $defaultData = [
                "chat_id" => $id,
                "state" => InitialState::class,
                "stepData" => [],
            ];
            $data = json_encode($defaultData, JSON_OBJECT_AS_ARRAY);
            file_put_contents($this->filePath, $data);
        }

        $data = json_decode(file_get_contents($this->filePath), true);

        $this->state = $data['state'];
        $this->stepData = $data['stepData'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
        $this->updateField('state', $state);
    }

    public function getStepData()
    {
        return $this->stepData;
    }

    public function setStepData($text, $key = null)
    {
        if ($key) {
            $this->stepData[$key] = $text;
        } else {
            array_push($this->stepData, $text);
        }

        $this->updateField('stepData', $this->stepData);
    }

    public function flushData()
    {
        $this->stepData = [];
        $this->updateField('stepData', []);
    }

    private function updateField($fieldName, $value)
    {
        $data = json_decode(file_get_contents($this->filePath), true);
        $data[$fieldName] = $value;
        file_put_contents(
            $this->filePath,
            json_encode($data, JSON_OBJECT_AS_ARRAY)
        );
    }
}
