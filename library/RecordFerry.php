<?php

require_once 'RecordFerry/Table.php';


class RecordFerry
{

	public function __construct()
	{
		// construct DB adapter
		// construct serialisation lib
	}

	public function copyRows($source, $target)
	{
		// TODO EXTRACT: Source/Target logic into RecordFerry_Source/Target_... plugins
		// TODO: "Smarts" to turn array into RecordFerry_Source/Target derivative if required.

		$source_conn = $this->getConnection($source);
		$target_conn = $this->getConnection($target);

		// TODO HACK: Move out to RecordFerry_Source_... plugin, take DB name explicitly
		preg_match('/dbname=([a-zA-Z0-9_]*);?/', $source['dsn'], $matches);
		$source_db_name = $matches[1];

		$graph = $this->getDependencyGraph($source_conn, $source_db_name);
		var_dump($graph);

		// We got us some nodes. Start collecting rows to copy, recursively looking up and down our
		// tree / graph.
	}

	
	protected function getDependencyGraph(&$conn, $db_name)
	{
//		$table_names        = $this->getTablesForSchema($conn, $db_name);
		$table_foreign_keys = $this->getForeignKeysForSchema($conn, $db_name);
		$nodes = array();

		// Set up the Table nodes, keyed by the table name 
		foreach ($table_foreign_keys as $name => $foreign_keys) {
			$nodes[$name] =& new RecordFerry_Table($name);
		}

		// Set up dependencies between each node
		foreach ($table_foreign_keys as $name => $foreign_keys) {
			foreach ($foreign_keys as $key) {
				$nodes[$name]->addDependency($key['local_column'], $nodes[$key['remote_table']], $key['remote_column']);
			}
		}
		return $nodes;
	}


	// TODO: Abstract out to RecordFerry_Platform_DB
	protected function getForeignKeysForSchema(&$conn, $db_name)
	{
		// TODO: Break column information out, eg. type, auto_increment?
		$sql = "SELECT DISTINCT
					k.`table_name`, k.`column_name`, k.`referenced_table_name`, k.`referenced_column_name`
					/*!50116 , c.update_rule, c.delete_rule */
				FROM
					information_schema.key_column_usage k
					/*!50116 INNER JOIN information_schema.referential_constraints c
						ON c.constraint_name = k.constraint_name
					*/
/*					INNER JOIN information_schema.columns cl
						ON cl.*/
				WHERE
					k.table_schema = :db_name 
					/*!50116 AND c.constraint_schema = :db_name */";
					//AND k.`REFERENCED_COLUMN_NAME` is not NULL";
		$statement = $conn->prepare($sql);
		$statement->execute(array(':db_name' => $db_name));
		$keys = $statement->fetchAll();

		// Transform the flat list into a hash map, array('table_name' => array(of,foreign,keys)
		$keys_by_table = array();
		foreach ($keys as $key) {
			if (!isset($keys_by_table[$key['table_name']])) {
				$keys_by_table[$key['table_name']] = array();
			}

			if (!empty($key['referenced_table_name']) && !empty($key['referenced_column_name'])) {
				$keys_by_table[$key['table_name']][] = array(
					'local_column' => $key['column_name'],
					'remote_table' => $key['referenced_table_name'],
					'remote_column' => $key['referenced_column_name']
				);
			}
		}
		
		// TODO EXTRACT
		/*
		$sql = "SELECT c.table_name, c.extra
				FROM information_schema.columns c 
				WHERE
					c.table_schema = :db_name
					c.table_name
				";
		*/
		return $keys_by_table;
	}


	// TODO: Abstract connection construction out to RecordFerry_Platform_DB
	protected function &getConnection($config)
	{
		$username = NULL;
		$password = NULL;
		if (isset($config['username'])) {
			$username = $config['username'];
			if (isset($config['password'])) {
				$password = $config['password'];
			}
		}
		$connection = new PDO($config['dsn'], $username, $password);
		return $connection;
	}



	// TODO: None of this belongs here, and shouldn't be called directly
	//       by someone using RecordFerry.
	public function resolveDependency(RecordFerry_Node &$node)
	{

		$resolved   = array();
		$unresolved = array();
		$this->_resolveDependency($node, $resolved, $unresolved);

		var_export($resolved);
	}

	protected function _resolveDependency(RecordFerry_Node &$node, array &$resolved, array &$unresolved)
	{
		$unresolved[$node->name] =& $node;
		foreach ($node->dependencies as $dep) {
			if (!array_key_exists($dep->name, $resolved)) {
				if (array_key_exists($dep->name, $unresolved)) {
					throw new Exception(sprintf('Circular reference: %s --> %s', $node->name, $dep->name));
				}
				$this->_resolveDependency($dep, $resolved, $unresolved);
			}
		}
		$resolved[$node->name] =& $node;
		unset($unresolved[$node->name]);
	}

}


// Brainstorming: setting dependencies.
/*
	$order        = new RecordFerry_Node('order');
	$ticket       = new RecordFerry_Node('ticket');
	$ticket_type  = new RecordFerry_Node('ticket_type');
	$order_action = new RecordFerry_Node('order_action');

	$order_action->addDependency($order);
	$ticket->addDependency($order);
	$ticket->addDependency($ticket_type);

	$rf = new RecordFerry();
	$rf->resolveDependency($order);
*/

// A Rough Plan:
//
// 1) Pick an arbitrary record (or group of records, via query)
// 2) Determine dependency tree of records relative to that record.
// 3) Serialise, transmit and unserialise tree of records (via XML, JSONP, EzyComponents hydration, serialize(), etc).
// 4) Start at top of tree, insert records, replacing points of dependency (eg. overwriting ticket's order_id to match the new record).
// 5) ???
// 6) Take over world.
