<?php defined('SYSPATH') or die('No direct access allowed.');
class ReportParams extends Bluebox_Record {
	public function setUp() {

		$this->hasOne('Report', array('local' => 'report', 'foreign' => 'report_id'));

		$this->actAs('GenericStructure');
	}

	public function setTableDefinition() {
		$this->hasColumn('reportparam_id','integer',11,array('unsigned'=>true,'primary'=>true,'autoincrement'=>true));
		$this->hasColumn('report', 'integer', 11);
		$this->hasColumn('name', 'string', 255); // description on interface
		$this->hasColumn('paramnumber','integer'); // Order of parameters passed with query
		$this->hasColumn('displaynumber','integer'); // Order to ask questions
		$this->hasColumn('kind','string',30); // e.g. "string", "integer", "multichoice", could also include "device" etc?
		$this->hasColumn('defaultvalue','string',255); // default value; may have special values like 'now' for kind='date'
		$this->hasColumn('multichoice','string',1024); // kind=multichoice only; may be json(array(key=>value)), or sql string.
	}
}


