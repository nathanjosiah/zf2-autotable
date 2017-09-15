<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BaseTable implements TableInterface {
	public $tableGateway,$primaryColumn,$idProperty,$tablesConfig;

	/**
	 * @param mixed $id
	 * @return mixed
	 */
	public function fetchWithId($id) {
		return $this->tableGateway->select([$this->primaryColumn=>$id])->current();
	}

	/**
	 * @param mixed $id
	 * @return mixed
	 */
	public function fetchWithFilter(array $filter) {
		return $this->tableGateway->select($filter);
	}

	public function fetchWithFilteredJoin(array $filter,string $local_table,string $mapping_table,string $local_column,string $local_mapping_column,string $remote_mapping_column,string $remote_column) {
		$select = $this->tableGateway->getSql()->select();
		$remote_table = $this->tableGateway->table;
		$select->columns(['*']);
		$select->join($mapping_table,$remote_table.'.'.$remote_column . ' = ' . $mapping_table.'.'.$remote_mapping_column,[],Select::JOIN_LEFT);
		$select->join($local_table,$local_table.'.'.$local_column . ' = ' . $mapping_table.'.'.$local_mapping_column,[],Select::JOIN_LEFT);
		$select->where($filter);
		return $this->executeJoinSelect($select);
	}

	protected function executeJoinSelect(Select $select) {
		return $this->tableGateway->selectWith($select);
	}

	/**
	 * @return mixed[]
	 */
	public function fetchAll() {
		return $this->tableGateway->select();
	}

	public function fetchPaginated(array $filter=null) : Paginator {
		$select = new Select($this->tableGateway->table);
		if($filter) {
			$select->where($filter);
		}
		$paginatorAdapter = new DbSelect(
			$select,
			$this->tableGateway->getAdapter(),
			$this->tableGateway->getResultSetPrototype()
		);
		$paginator = new Paginator($paginatorAdapter);
		return $paginator;
	}

	public function deleteWithId($int) : void {
		$this->tableGateway->delete([$this->primaryColumn=>$int]);
	}

	public function save(Proxy $object) : void {
		$data = $this->extractData($object);

		if($object->{$this->idProperty}) {
			$this->tableGateway->update($data,[$this->primaryColumn=>$object->{$this->idProperty}]);
		}
		else {
			unset($data[$this->primaryColumn]);
			$this->tableGateway->insert($data);
			$object->{$this->idProperty} = $this->tableGateway->getLastInsertValue();
		}
	}

	public function extractData(Proxy $object) {
		$data = $this->tableGateway->getResultSetPrototype()->getHydrator()->extract($object);

		$table_config = $this->tablesConfig[$object->__getTable()];

		if(!empty($table_config['linked_tables'])) {
			foreach($table_config['linked_tables'] as $property_name => $link_table) {
				if(!empty($link_table['should_save'])) {
					continue;
				}
				if(isset($link_table['alias_to']) || $link_table['type'] === 'one_to_many' || $link_table['type'] === 'many_to_many') {
					unset($data[$property_name]);
				}
			}
		}

		return $data;
	}


	public function setTableGateway(TableGateway $table_gateway) {
		$this->tableGateway = $table_gateway;
	}

	public function setPrimaryColumn(string $primary_column) {
		$this->primaryColumn = $primary_column;
	}

	public function setIdProperty(string $id_property) {
		$this->idProperty = $id_property;
	}

	public function setTablesConfig(array $tables_config) {
		$this->tablesConfig = $tables_config;
	}
}

