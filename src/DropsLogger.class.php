<?php

/**
 * Class DropsLogger
 */
class DropsLogger
{

    const DEBUG = 'debug';
    const INFO = 'info';
    const NOTICE = 'notice';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';

    protected $logFile;

    /**
     * @param string $logfile Filename to log messages to (complete path)
     * @throws InvalidArgumentException When logfile cannot be created or is not writeable
     */
    public function __construct($logfile)
    {

        if (!file_exists($logfile)) {
            if (!touch($logfile)) {
                throw new InvalidArgumentException('Log file ' . $logfile . ' cannot be created');
            }
        }

        if (!is_writable($logfile)) {
            throw new InvalidArgumentException('Log file ' . $logfile . ' is not writeable');
        }

        $this->logFile = DROPSHOME . '/logs/' . $logfile;

    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        /*
        * @var wpdb $dbConnection
        */
        global $wpdb;

        $logline = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level) . ': ' . $this->interpolate($message, $context) . "\n";
        //file_put_contents($this->logFile, $logline, FILE_APPEND | LOCK_EX);

        $wpdb->insert(
            Config::get('DB_DROPS_LOG'),
            array(
                'time' => date('Y-m-d H:i:s'),
                'level' => strtoupper($level),
                'message' => $logline
            )
        );

    }

    /**
     * Interpolates context values into the message placeholders.
     * This function is just copied from the example in the PSR-3 spec
     * @param $message
     * @param array $context
     * @return string
     */
    protected function interpolate($message, array $context = array())
    {

        // Build a replacement array with braces around the context keys

        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // Interpolate replacement values into the message and return
        return strtr($message, $replace);

    }

}