<?php

namespace AutoTable;

use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Hydrator\HydratorInterface;

class ResultSetFactory {
	public function create(HydratorInterface $hydrator,$objectPrototype,Proxy $proxyPrototype,string $table_name) : ResultSetInterface {
		return new HydratingResultSet($hydrator,$objectPrototype,$proxyPrototype,$table_name);
	}
}

