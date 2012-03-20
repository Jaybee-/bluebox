<?php defined('SYSPATH') or die('No direct access allowed.');
class Reporter_Configure extends Bluebox_Configure
{
    public static $version = 1.0;
    public static $packageName = 'reporter';
    public static $displayName = 'Reporter';
    public static $author = 'Jort Bloem';
    public static $vendor = 'BTG';
    public static $license = 'MPL';
    public static $summary = 'Report on arbitrary data in the database';
    public static $default = true;
    public static $type = Package_Manager::TYPE_MODULE;
    public static $required = array( 'core' => 0.1 );
    public static $navBranch = '/Status/';
    public static $navURL = '/reporter';
    public static $navSubmenu = array (
   );

}

