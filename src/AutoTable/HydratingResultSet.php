<?php

namespace AutoTable;

use Zend\Db\ResultSet\HydratingResultSet as ZendResultSet;
use Zend\Hydrator\HydratorInterface;

class HydratingResultSet extends ZendResultSet {
	// Enable the buffer so the results can be rewound. There are too many issues with this turned off.
	protected $buffer = [];

	public $proxyFactory,$tableName;
	public function __construct(HydratorInterface $hydrator,$objectPrototype,ProxyFactory $proxy_factory,string $table_name) {
		parent::__construct($hydrator,$objectPrototype);
		$this->proxyFactory = $proxy_factory;
		$this->tableName = $table_name;
	}

	public function current() {
		$current = parent::current();
		if(!$current) {
			return $current;
		}
		$proxy = $this->proxyFactory->create();
		$proxy->__setObject($current);
		$proxy->__setTable($this->tableName);
		return $proxy;
	}
}

