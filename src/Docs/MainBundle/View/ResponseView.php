<?php
namespace Docs\MainBundle\View;

/**
 * Container for rest response data
 * @author hbotev
 *
 */
class ResponseView
{
    /**
     * Contains the response data from the controller
     * @var mixed
     */
    protected $data;

    /**
     * Contains the error message
     * @var string
     */
    protected $error;

    /**
     * Http response code
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Return true if this is an error response
     * @return boolean
     */
    public function isError()
    {
        return !empty($this->error);
    }

    /**
     * Return the response result
     * @return mixed
     */
    public function getResult()
    {
        return $this->data;
    }

    /**
     * Set the response result
     * @param mixed $result
     * @return \Docs\MainBundle\View\ResponseView
     */
    public function setResult($result)
    {
        $this->data = $result;

        return $this;
    }

    /**
     * Return the response error
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the response error
     * @param string $e
     * @return \Docs\MainBundle\View\ResponseView
     */
    public function setError($e)
    {
        $this->error = $e;
        return $this;
    }

    /**
     * Return the http status code of the response
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set the http status code of the response
     * @param int $code
     * @return \Docs\MainBundle\View\ResponseView
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }
}
