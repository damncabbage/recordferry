<?php

// TODO: Move bootstrap elsewhere?
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__).'/../library'));


require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/DefaultTester.php';
require_once 'PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php';
require_once 'PHPUnit/Extensions/Database/Operation/Factory.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';


class RecordFerryTest extends PHPUnit_Framework_TestCase
{
	protected $db = array(
		'source' => array(
			'connection' => NULL,
			'tester' => NULL,
			'dsn' => 'mysql:host=localhost;dbname=recordferry_test_source',
			'schema' => 'recordferry_test_source',
			'username' => 'recordferry_test',
			'password' => 'recordferry_test',
		),
		'target' => array(
			'connection' => NULL,
			'tester' => NULL,
			'dsn' => 'mysql:host=localhost;dbname=recordferry_test_target',
			'schema' => 'recordferry_test_target',
			'username' => 'recordferry_test',
			'password' => 'recordferry_test',
		),
	);


	protected function setUpConnection($db_name)
	{
		$db = $this->db[$db_name];
		if (empty($db['connection'])) {
			$this->db[$db_name]['connection'] = new PDO($db['dsn'], $db['username'], $db['password']);
		}

		$tester_connection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($this->db[$db_name]['connection'], $db['schema']);
		$tester = new PHPUnit_Extensions_Database_DefaultTester($tester_connection);
		$this->db[$db_name]['tester'] =& $tester;

		return $this;
	}

	protected function setUpFixture($db_name, $fixture_name)
	{
		$tester = $this->db[$db_name]['tester'];
		$tester->setSetUpOperation(PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT());

		$fixture_prefix = sprintf('%s/%s.%s.%s', realpath(dirname(__FILE__).'/_fixtures'), get_class($this), $fixture_name, $db_name);

		$table_sql = file_get_contents($fixture_prefix.'.sql');

		// HACK: Incredibly hacky way to break up statements;
		$table_sql_statements = explode(';', $table_sql);
		foreach ($table_sql_statements as $statement) {
			$sql = trim($statement);
			if (!empty($sql)) {
				$this->db[$db_name]['connection']->exec($sql);
			}
		}

		$dataset = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($fixture_prefix.'.xml');
		$tester->setDataSet($dataset);

		$tester->onSetUp();
		return $this;
	}


	public function testCopySingleIndependentRecord()
	{
		$this->setUpConnection('source')
		     ->setUpFixture('source', __FUNCTION__);
	}


	public function testCopySingleDependentRecordWithDependencies()
	{
		$this->setUpConnection('source')
		     ->setUpFixture('source', __FUNCTION__)
		     ->setUpConnection('target')
		     ->setUpFixture('target', __FUNCTION__);

		require 'RecordFerry.php';
		$rf = new RecordFerry;
		$rf->copyRows(
			array(
				'dsn' => $this->db['source']['dsn'],
				'username' => $this->db['source']['username'],
				'password' => $this->db['source']['password'],
				'table' => 'post',
				'condition' => array('id' => 2),
			),
			array(
				'dsn' => $this->db['source']['dsn'],
				'username' => $this->db['source']['username'],
				'password' => $this->db['source']['password'],
			)
		);


	}
}
