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