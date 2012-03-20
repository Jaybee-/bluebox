<?php defined('SYSPATH') or die('No direct access allowed.');

class Reporter_Controller extends Bluebox_Controller {
	private function _get_report($sql,$params=array(),$links=TRUE, $htmlentities=TRUE) {
		print Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName();
		$fetch=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll($sql,$params);
		if (count($fetch)==0) {
			return;
		}
		$result=array(array());
		foreach ($fetch[0] AS $fieldname=>$dummy) {
			if (strpos($fieldname,"__link_")!==0) {
				$result[0][]=ucwords(str_replace("_"," ",$fieldname));
			}
		}
		foreach ($fetch AS $row) {
			$newline=array();
			foreach ($row AS $fieldname=>$value) {
				if (strpos($fieldname,"__link_")!==0) {
					if ($htmlentities) {
						$value=htmlentities($value);
					}
					if ((array_key_exists("__link_$fieldname",$row)) && ($row["__link_$fieldname"]!="") && $links) {
						$value=sprintf("<a href='%s'>%s</a>",$row["__link_$fieldname"],$value);
					}
					$newline[]=$value;
				}
			}
			$result[]=$newline;
		}
		return $result;
	}
	private function _get_report_html($sql,$params=array()) {
		$result="<table border=1>";
		foreach ($this->_get_report($sql,$params,TRUE,TRUE) AS $index=>$data) {
			if ($index==0) {
				$result.="<tr><th>".join("</th><th>",$data)."</th></tr>\n";
			} else {
				$result.="<tr><td>".join("</td><td>",$data)."</td></tr>\n";
			}
		}
		$result.="</table>";
		return $result;
	}
	private function _get_report_csv($sql,$params=array()) {
		$result="";
		foreach ($this->_get_report($sql,$params,FALSE,FALSE) AS $row) {
			foreach ($row AS $index=>$field) {
				if ($index>0) {
					$result.=",";
				}
				if (preg_match("/[\",\r\n]/",$field)) {
					$field='"'.str_replace(array('"'),array('""'),$field).'"';
				} else {
					$result.=$field;
				}
			}
			$result.="\r\n";
		}
		return $result;
	}
	public function index() {
		$sql='select class_type,count(*) from number group by class_type having count(*)>?';
		print $this->_get_report_html($sql,array(10));
		exit;

	}
}
