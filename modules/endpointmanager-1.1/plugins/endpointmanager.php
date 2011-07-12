<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @author Your Name <your@email.org>
 * @license Your License
 * @package _Skeleton
 */
class EndpointManager_Plugin extends Bluebox_Plugin
{
    protected $name = 'endpointdevice';

    public function addPluginData() {
        parent::addPluginData();

        include_once(MODPATH . 'endpointmanager-1.1' . DIRECTORY_SEPARATOR . "functions.php");
        $phone_info = prepare_phone_info($this->base);
	if ($phone_info!==false) {
                $endpoint->prepare_configs($phone_info);
        	return true;
         } else {
                return false;
        }
    }

}
