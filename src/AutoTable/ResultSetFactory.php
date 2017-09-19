<?php

namespace AutoTable;

use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Hydrator\HydratorInterface;

class ResultSetFactory {
	public function create(HydratorInterface $hydrator,$objectPrototype,ProxyFactory $proxyFactory,string $table_name) : ResultSetInterface {
		return new HydratingResultSet($hydrator,$objectPrototype,$proxyFactory,$table_name);
	}
}

