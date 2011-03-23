<?php defined('SYSPATH') or die('No direct access allowed.');

class FlexiRoute_Controller extends BlueBox_Controller
{
	public function index()
	{
		$this->view->froutes=array();
		$this->view->routes_hash=array();
		$this->view->nextid=0;
		foreach (Doctrine::getTable('FlexiRoute')->findAll() AS $froute) {
			/* Note: these have a specific naming:
				fields that are part of the FlexiRoute object are called
				by their exact names. Other fields start with '_'.
			*/
			$arr=array(
				"flexiroute_id"=>$froute->flexiroute_id,
				"prefix"=>$froute->prefix,
				"trunk_id"=>$froute->trunk_id,
				"_TrunkName"=>$froute->Trunk->name,
				"simple_route_id"=>$froute->simple_route_id,
				"_RouteName"=>$froute->SimpleRoute->name,
				"context_id"=>$froute->context_id,
				"_ContextName"=>$froute->Context->name,
				"priority"=>$froute->priority,
				"_state"=>"unmodified",
			);
			array_push($this->view->froutes,$arr);
			$this->view->routes_hash[$arr["flexiroute_id"]]=$arr;
			
			if ($froute->flexiroute_id>=$this->view->nextid) {
				$this->view->nextid=$froute->flexiroute_id+1;
			}
		}
		usort($this->view->froutes,
			function($a, $b) { return ($a["priority"] < $b["priority"]) ? -1 : 1;}
		);

		$this->view->contexts=array();
		$this->view->contextoptions="";
		foreach (Doctrine::getTable('Context')->findAll() AS $context) {
			$this->view->contexts[$context->context_id]=$context->name;
			$this->view->contextoptions.="<option value='".$context->context_id."'>".$context->name.'</option>';
		}
		$this->view->trunk=array();
		$this->view->trunkoptions="";
		foreach (Doctrine::getTable('Trunk')->findAll() AS $trunk) {
			$this->view->trunk[$trunk->context_id]=$trunk->name;
			$this->view->trunkoptions.="<option value='".$trunk->trunk_id."'>".$trunk->name.'</option>';
		}

		$this->view->routes=array();
		$this->view->routeoptions="";
		foreach (Doctrine::getTable('SimpleRoute')->findAll() AS $route) {
			$this->view->routes[$route->simple_route_id]=$route->name;
			$this->view->routeoptions.="<option value='".$route->simple_route_id."'>".$route->name.'</option>';
		}
	}
}
