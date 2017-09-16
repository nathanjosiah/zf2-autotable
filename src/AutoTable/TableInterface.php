<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGateway;

interface TableInterface {
	public function setTableGateway(TableGateway $table_gateway);
	public function setPrimaryColumn(string $primary_column);
	public function setIdProperty(string $id_property);
	public function setTablesConfig(array $tables_config);
	public function save(Proxy $object) : void;
	public function deleteWithId(int $id);
	public function fetchWithId(int $id);
	public function fetchWithFilter(array $filter);
	public function fetchWithFilteredJoin(array $filter,string $local_table,string $mapping_table,string $local_column,string $local_mapping_column,string $remote_mapping_column,string $remote_column);
}

