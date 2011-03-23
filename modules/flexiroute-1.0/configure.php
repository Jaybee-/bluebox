<?php defined('SYSPATH') or die('No direct access allowed.');

class FlexiRoute_1_0_Configure extends Bluebox_Configure
{
    public static $version = 1.0;
    public static $packageName = 'flexiroute';
    public static $displayName = 'Flexi Route';
    public static $author = 'J Bloem';
    public static $vendor = 'BTG';
    public static $license = 'MPL';
    public static $summary = 'Flexi Route';
    public static $description = 'A more advanced routing mechanism for trunk modules';
    public static $default = false;
    public static $type = Package_Manager::TYPE_PLUGIN;
    public static $required = array(
        'core' => 0.1,
        'trunkmanager' => 0.1,
	'simpleroute' => 1.1,
    );
    public static $navBranch = '/Connectivity/';
    public static $navURL = 'flexiroute/index';
    public static $navSubmenu = array(
        'Flexi Routes' => '/flexiroute/index',
    );
    
    public function postInstall()
    {
    }

    public function migrate()
    {
        $conn = Doctrine_Manager::connection();

        if (!$conn->import->tableExists(array('flexi_route')))
        {
            Doctrine::createTablesFromArray(array('FlexiRoute'));

            $this->postInstall();
        }
    }
}
