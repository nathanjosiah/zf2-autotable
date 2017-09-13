<?php

namespace AutoTable;

use Zend\Db\ResultSet\HydratingResultSet as ZendResultSet;
use Zend\Hydrator\HydratorInterface;

class HydratingResultSet extends ZendResultSet {
	public $proxyPrototype,$tableName;
	public function __construct(HydratorInterface $hydrator,$objectPrototype,Proxy $proxyPrototype,string $table_name) {
		parent::__construct($hydrator,$objectPrototype);
		$this->proxyPrototype = $proxyPrototype;
		$this->tableName = $table_name;
	}

	public function current() {
		$current = parent::current();
		if(!$current) {
			return $current;
		}
		$proxy = clone $this->proxyPrototype;
		$proxy->__setObject($current);
		$proxy->__setTable($this->tableName);
		return $proxy;
	}
}

