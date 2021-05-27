<?php

abstract class State
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var bool
     */
    protected $hasError = false;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var bool
     */
    protected $omitResponse = false;

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return $this
     */
    public function setOmitResponse()
    {
        $this->omitResponse = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function omitResponse() {
        return $this->omitResponse;
    }

    /**
     * @param $errorMessage
     */
    public function setError($errorMessage)
    {
        $this->hasError = true;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function hasError() {
        return $this->hasError;
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    abstract public function handleRequest(): void;

    abstract public function sendData(): void;
}
