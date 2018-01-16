<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 16.11.2017
 * Time: 00:50
 */
class DropsStatistics extends DropsMain
{
    private $defaultFilterData;

    /**
     * DropsSettings constructor.
     */
    public function __construct()
    {
        $this->applyFilter($this->getFilterData());

        $this->defineTemplateVariables();
    }

    private function defineTemplateVariables()
    {

        $this->page['settings']['dropsClientId'] = array(
            'title' => __('Client ID', 'drops'),
            'value' => isset($_POST['dropsClientId']) ? $_POST['dropsClientId'] : get_option( 'dropsClientId' ),
            'description' => __('The client id is sent to the drops microservice to authenticate the plugin to receive the access token for further communication', 'drops')
        );

        $this->page['settings']['dropsUserAccessHash'] = array(
            'title' => __('Authentication key', 'drops'),
            'value' => isset($_POST['dropsUserAccessHash']) ? $_POST['dropsUserAccessHash'] : get_option( 'dropsUserAccessHash' ),
            'description' => __('The key from the drops microservice to authenticate the service. When a profile is created in the drops microservice, it pushes the user\'s data to this plugin, which adds the user to wordpress', 'drops')
        );

        // URLS for the connection to drops

        $this->page['settings']['dropsLoginUrl'] = array(
            'title' => __('Login URL of drops', 'drops'),
            'value' => isset($_POST['dropsLoginUrl']) ? $_POST['dropsLoginUrl'] : get_option( 'dropsLoginUrl' ),
            'description' => __('The user will be redirected to this page to login there', 'drops')
        );

        $this->page['settings']['dropsAuthUrl'] = array(
            'title' => __('Authentication URL of drops', 'drops'),
            'value' => isset($_POST['dropsAuthUrl']) ? $_POST['dropsAuthUrl'] : get_option( 'dropsAuthUrl' ),
            'description' => __('The user will be redirected to this page to receive the authorization code', 'drops')
        );

        $this->page['settings']['dropsAccessUrl'] = array(
            'title' => __('Access token URL of drops', 'drops'),
            'value' => isset($_POST['dropsAccessUrl']) ? $_POST['dropsAccessUrl'] : get_option( 'dropsAccessUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to receive the access token for the user', 'drops')
        );

        $this->page['settings']['dropsUserProfileUrl'] = array(
            'title' => __('Profile URL of drops', 'drops'),
            'value' => isset($_POST['dropsUserProfileUrl']) ? $_POST['dropsUserProfileUrl'] : get_option( 'dropsUserProfileUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to get the login user informations', 'drops')
        );

        $this->page['settings']['dropsUserDeleteUrl'] = array(
            'title' => __('Delete User URL of drops', 'drops'),
            'value' => isset($_POST['dropsUserDeleteUrl']) ? $_POST['dropsUserDeleteUrl'] : get_option( 'dropsUserDeleteUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to trigger delete action for an user', 'drops')
        );

        $this->page['settings']['dropsUserReadUrl'] = array(
            'title' => __('Read URL of drops', 'drops'),
            'value' => isset($_POST['dropsUserReadUrl']) ? $_POST['dropsUserReadUrl'] : get_option( 'dropsUserReadUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to trigger read action', 'drops')
        );

        $this->page['settings']['dropsUserUpdateUrl'] = array(
            'title' => __('Update URL of drops', 'drops'),
            'value' => isset($_POST['dropsUserUpdateUrl']) ? $_POST['dropsUserUpdateUrl'] : get_option( 'dropsUserUpdateUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to trigger update action', 'drops')
        );

        $this->page['settings']['dropsUserImageUpdateUrl'] = array(
            'title' => __('Update URL of drops (Profile Image)', 'drops'),
            'value' => isset($_POST['dropsUserImageUpdateUrl']) ? $_POST['dropsUserImageUpdateUrl'] : get_option( 'dropsUserImageUpdateUrl' ),
            'description' => __('The drops wordpress plugin will call this URL to trigger update action', 'drops')
        );

    }

    public function render()
    {
        include 'templates/dropsStatistics.php';
    }

    private function applyFilter($filterArray = array())
    {

        /** @var wpdb $wpdb */
        global $wpdb;

        $filter = "SELECT * FROM `" . Config::get('DB_META_TABLE') . "` WHERE login_time > '" . $filterArray['stat_from'] . "' AND login_time <= '" . $filterArray['stat_to'] . "' ORDER BY login_time";

        $metaInformations = $wpdb->get_results($filter);

        $this->page['statistics']['logins'] = $metaInformations;

    }

    private function getFilterData()
    {

        $this->page['statistics']['from'] = 'yyyy-mm-dd';
        $this->page['statistics']['to'] = 'yyyy-mm-dd';

        $values = [
            'stat_from' => date('Y-m-d', strtotime('-500 days')),
            'stat_to' => 'now()',
        ];

        if ($_POST) {

            $fromDate = explode('-', $_POST['stat_from']);
            $toDate = explode('-', $_POST['stat_to']);

            if (count($fromDate) != 3 || count($toDate) != 3) {
                die('HERE: ' . __LINE__);
                return $values;
            }

            if (!is_numeric($fromDate[0]) || !is_numeric($fromDate[1]) || !is_numeric($fromDate[2])
            || !is_numeric($toDate[0]) || !is_numeric($toDate[1]) || !is_numeric($toDate[2])) {
                return $values;
            }

            checkdate($fromDate[1], $fromDate[2], $fromDate[0]);
            checkdate($toDate[1], $toDate[2], $toDate[0]);

            $this->page['statistics']['from'] = $_POST['stat_from'];
            $this->page['statistics']['to'] = $_POST['stat_to'];

            $values['stat_from'] = $_POST['stat_from'];
            $values['stat_to'] = $_POST['stat_to'];

        }

        return $values;

    }

}