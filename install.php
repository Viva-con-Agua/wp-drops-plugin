<?php

function installDropsDatabase()
{
    global $wpdb;

    $wpdb->query("CREATE TABLE IF NOT EXISTS `" . Config::get('DB_META_TABLE') . "` (
        `login_time` varchar(32) NOT NULL,
        `login_count` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
    );

    $wpdb->query("CREATE TABLE IF NOT EXISTS `" . Config::get('DB_SESSION_TABLE') . "` (
          `temporary_session_id` varchar(64) NOT NULL,
          `user_id` int(11) NOT NULL DEFAULT '0',
          `user_session` text NOT NULL,
          `drops_session_id` varchar(64) NOT NULL,
          `token_type` varchar(64) DEFAULT NULL,
          `access_token` text NOT NULL,
          `refresh_token` varchar(64) DEFAULT NULL,
          `expiry_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
    );

    $wpdb->query("CREATE TABLE `" . Config::get('DB_DROPS_LOG') . "` (
          `id` int(11) NOT NULL,
          `time` varchar(32) NOT NULL,
          `level` varchar(16) NOT NULL,
          `message` text NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
    );
}

function removeDropsDatabase()
{
    global $wpdb;
    $wpdb->query("DROP TABLE `" . Config::get('DB_SESSION_TABLE') . "`");
    $wpdb->query("DROP TABLE `" . Config::get('DB_SESSION_TABLE') . "`");
}

register_activation_hook( PLUGINROOTFILE, 'installDropsDatabase' );
register_deactivation_hook( PLUGINROOTFILE, 'removeDropsDatabase' );

?>