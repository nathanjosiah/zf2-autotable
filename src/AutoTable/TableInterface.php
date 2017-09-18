<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGatewayInterface;

interface TableInterface {
	public function setTableGateway(TableGatewayInterface $table_gateway);
	public function setPrimaryColumn(string $primary_column);
	public function setIdProperty(string $id_property);
	public function setTablesConfig(array $tables_config);
	public function save(Proxy $object) : void;
	public function deleteWithId($id);
	public function fetchWithId($id);
	public function fetchWithFilter(array $filter);
	public function fetchWithFilteredJoin(array $filter,string $local_table,string $mapping_table,string $local_column,string $local_mapping_column,string $remote_mapping_column,string $remote_column);
}

