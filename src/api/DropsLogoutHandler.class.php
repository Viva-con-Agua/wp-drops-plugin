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

            $connectionOptions = new \Nats\ConnectionOptions();
            $connectionOptions->setHost($natsServer)->setPort(4222);

            $client = new \Nats\Connection($connectionOptions);
            $client->connect();

            $client->subscribe(
                'LOGOUT',
                function ($payload) {
                    printf("Data: %s\r\n", $payload->getBody()[1]);
                }
            );

        }

    }
}