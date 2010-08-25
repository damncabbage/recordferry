<?php

class RecordFerry_Table
{
	public $name;
	public $dependencies = array();

	public function __construct($name)
	{
		$this->name  = $name;
	}

	public function addDependency(RecordFerry_Table $node)
	{
		$this->dependencies[] =& $node;
		
	}

}
