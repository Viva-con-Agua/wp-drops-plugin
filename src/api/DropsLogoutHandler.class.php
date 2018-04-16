<?php

require_once DROPSHOME . '/src/api/nats/vendor/autoload.php';

/**
 * Class DropsConnector
 */
class DropsLogoutHandler
{

    public function __construct()
    {

        $natsServer = Config::get('NATS_SERVER');

        if (!empty($natsServer)) {


            $logLine = 'LOGOUT EVENT LISTENER STARTED';
            (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);

            try {

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
                        wp_logout();
                        $logLine = 'LOGOUT EVENT TRIGGERED: ' . print_r($payload, true);
                        (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);
                    }
                );

                //$client->wait(0);

            } catch (Exception $e) {
                (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::ERROR, $e->getMessage());
            }

        } else {
            $logLine = 'LOGOUT EVENT LISTENER COULD NOT BE STARTED';
            (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);
        }

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

    private function handleLogoutEvent($payload)
    {

        $logLine = 'LOGOUT EVENT TRIGGERED: ' . print_r($payload, true);

        (new DropsLogger(date('Y_m_d') . '_' . Config::get('DROPS_LOGFILE')))->log(DropsLogger::INFO, $logLine);

        //do_action('wp_login', $user->user_login, $user);
    }
}