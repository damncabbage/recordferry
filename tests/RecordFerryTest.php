<?php

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

		$fixture_prefix = sprintf('%s/%s.%s', realpath(dirname(__FILE__).'/_fixtures'), $fixture_name, $db_name);

		$table_sql = file_get_contents($fixture_prefix.'.sql');
		$this->db[$db_name]['connection']->exec($table_sql);

		$dataset = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($fixture_prefix.'.xml');
		$tester->setDataSet($dataset);

		$tester->onSetUp();
		return $this;
	}


	public function testCopySingleIndependentRecord()
	{
		$this->setUpConnection('source')
		     ->setUpFixture('source', 'SingleIndependentRecord');
	}
}
