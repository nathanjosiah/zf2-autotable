<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGateway;

interface TableInterface {
	public function setTableGateway(TableGateway $table_gateway);
	public function setPrimaryColumn(string $primary_column);
	public function setIdProperty(string $id_property);
	public function setTablesConfig(array $tables_config);
}

