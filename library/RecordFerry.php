<?php

class RecordFerry
{

	public function __construct()
	{
		// construct DB adapter
		// construct serialisation lib
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
