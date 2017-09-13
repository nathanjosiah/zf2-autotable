<?php

namespace AutoTable;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BaseTable {
	public $tableGateway,$primaryColumn,$idProperty;

	public function __construct(TableGateway $table_gateway,string $primary_column=null,string $id_property=null) {
		$this->tableGateway = $table_gateway;
		$this->primaryColumn = $primary_column ?? 'id';
		$this->idProperty = $id_property ?? 'id';
	}

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
		//echo $this->tableGateway->getSql()->buildSqlString($select);
		//exit;
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

	public function save($object) : void {
		$data = $this->tableGateway->getResultSetPrototype()->getHydrator()->extract($object);

		if($object->{$this->idProperty}) {
			$this->tableGateway->update($data,[$this->primaryColumn=>$object->{$this->idProperty}]);
		}
		else {
			$this->tableGateway->insert($data);
			$object->{$this->idProperty} = $this->tableGateway->getLastInsertValue();
		}
	}
}

