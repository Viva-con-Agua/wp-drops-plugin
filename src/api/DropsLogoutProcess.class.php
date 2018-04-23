<?php

require_once DROPSHOME . '/src/api/nats/vendor/autoload.php';

/**
 * Class DropsConnector
 */
class DropsLogoutProcess extends Thread
{

    /** @var  DropsSessionDataHandler $sessionDataHandler */
    private $sessionDataHandler;

    private $pidFileRoot = './processes.pid';

    public function __construct() {
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

	function removePidFile() {
        unlink($this->pidFileRoot);
    }

    function isProcessRunning() {
        if (!file_exists($this->pidFileRoot) || !is_file($this->pidFileRoot)) return false;
        $pid = file_get_contents($this->pidFileRoot);
        return posix_kill($pid, 0);
    }

    public function run() {

        if ($this->isRunning()) {
            return;
        }

        $natsServer = Config::get('NATS_SERVER');

        if (!empty($natsServer)) {

            $logLine = 'LOGOUT EVENT LISTENER STARTED';
            (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);

            try {

                file_put_contents($this->pidFileRoot, posix_getpid());
                register_shutdown_function('removePidFile');

                $connectionOptions = new \Nats\ConnectionOptions();
                $connectionOptions->setHost($natsServer)->setPort(4222);

                $client = new \Nats\Connection($connectionOptions);
                $client->connect();

                if ($client->isConnected()) {
                    (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, 'Connection established');
                }

                $client->subscribe(
                    'LOGOUT',
                    function ($payload) {

                        if (isset($payload->body)) {

                            $uuid = $payload->body;
                            $this->sessionDataHandler->clearSessionsByDropsId($uuid);

                        }

                        wp_logout();

                        $logLine = 'LOGOUT EVENT TRIGGERED: ' . print_r($payload, true);
                        (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);

                    }
                );

                $client->wait();

            } catch (Exception $e) {
                $this->removePidFile();
                (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::ERROR, $e->getMessage());
            }

        } else {
            $logLine = 'LOGOUT EVENT LISTENER COULD NOT BE STARTED';
            (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);
        }

    }

}