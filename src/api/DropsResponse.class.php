<?php

/**
 * Class DropsResponse
 */
class DropsResponse
{
    const ARR = 'array';
    const JSON = 'json';

    /**
     * @var string $context
     */
    private $context;

    /**
     * @var int $code
     */
    private $code;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $format
     * @return array|mixed|string|void
     */
    public function getFormat($format)
    {

        switch ($format) {
            case self::ARR:
                return get_object_vars($this);
                break;
            case self::JSON:
            default:
                return json_encode(get_object_vars($this));
                break;
        }

    }

}