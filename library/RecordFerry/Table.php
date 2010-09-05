<?php

class RecordFerry_Table
{
	public $name;

	public $dependencies = Array(); // Nodes we rely on
	public $dependents   = Array(); // Nodes that rely on us

	public function __construct($name)
	{
		$this->name  = $name;
	}

	public function addDependency($local_column, RecordFerry_Table $remote_table, $remote_column)
	{
		$this->dependencies[$local_column][$remote_table->name] = array(&$remote_table, $remote_column);
		$remote_table->addDependent($remote_column, $this, $local_column);
	}

	public function addDependent($local_column, RecordFerry_Table $remote_table, $remote_column)
	{
		$this->dependents[$local_column][$remote_table->name] = array(&$remote_table, $remote_column);
	}

}
