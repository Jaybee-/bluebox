<?php defined('SYSPATH') or die('No direct access allowed.');

class FlexiRoute_Controller extends BlueBox_Controller
{
	static function _sortcallback($a, $b) {
		return ($a["priority"] < $b["priority"]) ? -1 : 1;
	}
	public function index()
	{
		if (!array_key_exists("submitdata",$_REQUEST)) {
		} elseif ($_REQUEST["submitdata"]=="") {
			message::set("No changes detected", 'alert');
		} else {
			$updates=0;
			if (get_magic_quotes_gpc()) {
				$_REQUEST["submitdata"]=stripcslashes($_REQUEST["submitdata"]); 
			}
			foreach (json_decode(str_replace('\"','"',$_REQUEST["submitdata"])) AS $record) {
				if ($record->_state=="modified") {
					$dbrec=Doctrine::getTable("FlexiRoute")->find($record->flexiroute_id);
					$dbrec->dialstring=$record->dialstring;
					$dbrec->trunk_id=$record->trunk_id;
					$dbrec->simple_route_id=$record->simple_route_id;
					$dbrec->context_id=$record->context_id;
					$dbrec->priority=$record->_newpriority;
					$dbrec->save();
					$updates++;
				} elseif ($record->_state=="new") {
					$dbrec=new FlexiRoute;
					$dbrec->dialstring=$record->dialstring;
					$dbrec->trunk_id=$record->trunk_id;
					$dbrec->simple_route_id=$record->simple_route_id;
					$dbrec->context_id=$record->context_id;
					$dbrec->priority=$record->_newpriority;
					$dbrec->save();
					$updates++;
				} elseif ($record->_state=="deleted") {
					$dbrec=Doctrine::getTable("FlexiRoute")->find($record->flexiroute_id)->delete();
					$updates++;
				} elseif ($record->_newpriority != $record->priority) {
					$dbrec=Doctrine::getTable("FlexiRoute")->find($record->flexiroute_id);
					$dbrec->priority=$record->_newpriority;
					$dbrec->save();
					$updates++;
				}
			}
			if ($updates>0) {
				message::set("Changes saved", 'success');
			} else {
				message::set("No changes detected", 'alert');
			}
		}

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
				"dialstring"=>$froute->dialstring,
				"trunk_id"=>$froute->trunk_id,
				"_TrunkName"=>$froute->Trunk->name,
				"simple_route_id"=>$froute->simple_route_id,
				"_RouteName"=>$froute->SimpleRoute->name,
				"context_id"=>$froute->context_id,
				"_ContextName"=>$froute->Context->name,
				"priority"=>$froute->priority,
				"_newpriority"=>$froute->priority,
				"_state"=>"unmodified",
			);
			array_push($this->view->froutes,$arr);
			$this->view->routes_hash[$arr["flexiroute_id"]]=$arr;
			
			if ($froute->flexiroute_id>=$this->view->nextid) {
				$this->view->nextid=$froute->flexiroute_id+1;
			}
		}
		usort($this->view->froutes,array("FlexiRoute_Controller","_sortcallback"));

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
