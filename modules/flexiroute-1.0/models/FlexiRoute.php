<?php
class FlexiRoute extends Bluebox_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('flexiroute_id','integer',11, 
			array('unsigned' => true, 'notnull' => true, 
			'primary' => true, 'autoincrement' => true));
		$this->hasColumn('dialstring','string',32);
		$this->hasColumn('trunk_id','integer',11,
			array('notnull'=>TRUE,'unsigned'=>TRUE));
		$this->hasColumn('simple_route_id','integer',11,
			array('notnull'=>TRUE,'unsigned'=>TRUE));
		$this->hasColumn('context_id','integer',11,
			array('notnull'=>TRUE,'unsigned'=>TRUE));
		$this->hasColumn('priority','integer',11,
			array('notnull'=>TRUE,'unsigned'=>TRUE,'unique'=>TRUE));
	}
	public function setUp()
	{
		$this->hasOne('Trunk',
			array('local'=>'trunk_id','foreign'=>'trunk_id'));
		$this->hasOne('SimpleRoute',
			array('local'=>'simple_route_id','foreign'=>'simple_route_id'));
		$this->hasOne('Context',
			array('local'=>'context_id','foreign'=>'context_id'));
	}
}

