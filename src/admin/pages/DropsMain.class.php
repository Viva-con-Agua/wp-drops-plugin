<?php

require_once 'DropsHome.class.php';
require_once 'DropsSettings.class.php';

class DropsMain
{

    const HOME = 'home';
    const STATISTICS = 'stat';
    const SETTINGS = 'settings';

    protected $page = array();

    public function home()
    {

        $this->defineTemplateVariables();

        $this->header();

        switch ($this->page['activeTab']) {
            case self::STATISTICS:
                //(new DropsMetaStatistics())->render();
                break;
            case self::SETTINGS:
                (new DropsSettings())->render();
                break;
            case self::HOME:
            default:
                (new DropsHome())->render();
                break;
        }

    }

    private function header()
    {
        include 'templates/dropsHeader.php';
        include 'templates/dropsTabs.php';
    }

    private function defineTemplateVariables()
    {
        $this->page['activeTab'] = isset($_GET['tab']) ? $_GET['tab'] : self::HOME;
        $this->page['tabs'] = $this->getTabs();
        $this->page['url'] = '?page=drops';
    }

    private function getTabs()
    {
        return array(
            array(
                'value' => self::HOME,
                'icon' => 'icon-tasks',
                'title' => _x( 'Home', 'Home Admin Menu', 'drops' )
            ),
            array(
                'value' => self::STATISTICS,
                'icon' => 'icon-stats',
                'title' => _x( 'Statistics', 'Home Admin Menu', 'drops' )
            ),
            array(
                'value' => self::SETTINGS,
                'icon' => 'icon-settings',
                'title' => _x( 'Settings', 'Home Admin Menu', 'drops' )
            )
        );
    }

}