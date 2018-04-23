<?php

require_once DROPSHOME . '/src/api/nats/vendor/autoload.php';

/**
 * Class DropsConnector
 */
class DropsLogoutHandler
{

    /** @var  DropsSessionDataHandler $sessionDataHandler */
    private $sessionDataHandler;

    public function __construct()
    {
    }

    /**
     * Setter for the datahandler
     * @param SessionDataHandlerInterface $sessionDataHandler
     * @return $this
     */
    public function setSessionDataHandler(SessionDataHandlerInterface $sessionDataHandler)
    {
        $this->sessionDataHandler = $sessionDataHandler;
        return $this;
    }

    public function handleProcessing() {

        $process = (new DropsLogoutProcess())->setSessionDataHandler($this->sessionDataHandler);

        if (!$process->isRunning()) {
            $process->start();
        }

    }

}