<?php defined('SYSPATH') or die('No direct access allowed.');

class EndpointManager_Controller extends Bluebox_Controller
{
    protected $baseModel = 'EndpointDevice';
    protected $authBypass = array('config');

    public function generate($mac_address)
    {
        $this->auto_render = false;
    }

    public function config ($mac_address)
    {
	$file=func_get_args();
	array_shift($file); #first arg is mac_address - already captured as above
	$file=implode("/",$file);
	$file=str_replace("{mac}",$mac_address,$file);
	foreach (Doctrine::getTable('Device')->findAll() AS $extension) {
		if ($extension->{"plugins"}["endpointdevice"]["mac_address"]!=$mac_address) {
			continue;
		}
        	include_once(MODPATH . 'endpointmanager-1.1' . DIRECTORY_SEPARATOR . "functions.php");
		$endpoint=new endpointman();
	        $provisioner_lib= $endpoint->build_provisioner_lib($endpoint->prepare_phone_info($extension));
		$provisioner_lib->options["provisioning_type"]="http";
		$provisioner_lib->options["provisioning_path"]="/endpointmanager/config/$mac_address";
		$config=$provisioner_lib->generate_config();
		if (array_key_exists($file,$config)) {
			header("content-type: text/plain");
			print $config[$file];
		} else {
			foreach ($config AS $name=>$contents) {
				print "<a href='$name'>$name</a><br>\n";
			}
		}
	}
	exit;
    }
}
