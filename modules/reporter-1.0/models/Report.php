<?php defined('SYSPATH') or die('No direct access allowed.');
class Report extends Bluebox_Record {
	public function setUp() {
		$this->actAs('GenericStructure');
	}

	public function setTableDefinition() {
		$this->hasColumn('report_id','integer',11,array('unsigned'=>true,'primary'=>true,'autoincrement'=>true));
		$this->hasColumn('name', 'string', 255); // show in list of reports
		$this->hasColumn('title', 'string', 255); // report heading
		$this->hasColumn('notes', 'string', 255); // report footer
		$this->hasColumn('identifier', 'string', 32); // cross-install unique id, used for import/export. set to md5sum(current time). When editing, if parameters change, this changes.  Otherwise, operator has the oppertunity to change the identifier. If the identifier changes, mark this one as deleted, and add a new one.
		$this->hasColumn('lastmodified','timestamp'); // used for import/export, to see which version is the latest. If two reports with the same "identifier" collide, use the greatest lastmodified; if they are different identifiers, import the new one with a new name ("$oldname (2)")
		$this->hasColumn('deleted','boolean',null,array('default'=>false));
		$this->hasColumn('sql','string',1024); // SQL to run
		$this->hasColumn('grid','boolean',null,array('default'=>false)); // Future use
		$this->hasColumn('databaseengine','string'); // databaseengine this query is written for - Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName();
	}
}


