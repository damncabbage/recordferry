<?php

// Set up includes to point to library/
set_include_path(get_include_path().PATH_SEPARATOR.realpath(dirname(__FILE__).'/../../library'));

require_once 'RecordFerry.php';

$rf = new RecordFerry;

// Method 1: array shortcuts
$rf->copyRows(
	array(
		'dsn'       => 'mysql://host=localhost;dbname=rf_source',
		'username'  => 'example',
		'password'  => 'froghop',
		'table'     => 'posts',
		'condition' => array('id' => 1),
	),
	array(
		'dsn'       => 'mysql://host=localhost;dbname=rf_target',
		'username'  => 'example',
		'password'  => 'froghop',
		'tablemap'	=> array('posts' => 'tbl_post'),
	)
);


// Method 2: source / target objects
$source = new RecordFerry_Transfer_Source_MySQL();
$source->setHost('localhost')
       ->setSchemaName('rf_source')
       ->setUsername('example')
       ->setPassword('froghop')
       ->setTable('posts')
       ->setCondition('id', 1);

$target = new RecordFerry_Transfer_Target_MySQL();

$table_map = new RecordFerry_Transfer_TableMap();
$table_map->setMapping(
	'posts', 'tbl_post',
	array(
		'id'		=> 'post_id',
		'content'	=> 'post_content'
	)
);

$target->setHost('localhost')
       ->setSchemaName('rf_target')
       ->setUsername('example')
       ->setPassword('froghop')
       ->setTableMap($table_map);

$rf->copyRows($source, $target);
