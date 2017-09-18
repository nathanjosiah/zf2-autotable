<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\TableGateway\TableGateway;

class TableGatewayFactory {
	protected $adapter;
	public function __construct(AdapterInterface $adapter) {
		$this->adapter = $adapter;
	}
	public function create($table,$features=null,ResultSetInterface $resultSetPrototype=null) : TableGatewayInterface {
		return new TableGateway($table,$this->adapter,$features,$resultSetPrototype);
	}
}

