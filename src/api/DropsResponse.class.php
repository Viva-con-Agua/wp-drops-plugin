<?php

/**
 * Class DropsResponse
 */
class DropsResponse
{
    const ARR = 'array';
    const JSON = 'json';

    /**
     * @var array $response
     */
//    private $response;
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
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
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
     * Returns the response in the given format
     * @param $format
     * @return array|mixed|string
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