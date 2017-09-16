<?php

namespace AutoTable;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSetInterface;

class AutoTableManager {
	protected $config,$serviceLocator,$proxyPrototype,$unitOfWork;
	protected $tableCache = [];
	protected $work = [];

	public function __construct(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		$this->config = $serviceLocator->get('Config')['auto_tables'];
		$this->unitOfWork = new UnitOfWork($this,$this->config);
	}

	public function getTable(string $name) {
		// Waste not
		if(isset($this->tableCache[$name])) {
			return $this->tableCache[$name];
		}

		$table_config = $this->config[$name];
		$table_config['id_column'] = $table_config['id_column'] ?? 'id';
		$table_config['id_property'] = $table_config['id_property'] ?? 'id';

		$entity_class = $table_config['entity'];

		$hydrator = new HydratorProxy($this->serviceLocator->get($table_config['hydrator']));
		$proxy = new Proxy($this,$this->unitOfWork,$this->config);
		$this->proxyPrototype = $proxy;
		$entity = $this->getEntityForTable($name);
		$result_set = new HydratingResultSet($hydrator,$entity,$proxy,$name);
		$gateway = $this->createTableGateway($table_config['table_name'],$result_set);

		/* @var $table \AutoTable\TableInterface */
		$table = $this->serviceLocator->get($table_config['table'] ?? \AutoTable\BaseTable::class);

		$table->setTableGateway($gateway);
		$table->setPrimaryColumn($table_config['id_column']);
		$table->setIdProperty($table_config['id_property']);
		$table->setTablesConfig($this->config);

		$this->tableCache[$name] = $table;
		return $table;
	}

	public function createTableGateway(string $table,ResultSetInterface $result_set=null) : TableGateway {
		$adapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
		$gateway = new TableGateway($table,$adapter,[],$result_set);
		return $gateway;
	}

	private function getEntityForTable(string $table) {
		$table_config = $this->config[$table];
		$entity_class = $table_config['entity'];
		$entity = ($this->serviceLocator->has($entity_class) ? $this->serviceLocator->get($entity_class) : new $entity_class());
		return $entity;
	}

	public function createNew(string $table) : Proxy {
		$entity = $this->getEntityForTable($table);
		$proxy = clone $this->proxyPrototype;
		$proxy->__setObject($entity);
		$proxy->__setTable($table);
		$this->unitOfWork->registerCreate($proxy);
		return $proxy;
	}

	public function track($object,string $table) : Proxy {
		$proxy = clone $this->proxyPrototype;
		$proxy->__setObject($object);
		$proxy->__setTable($table);
		return $proxy;
	}

	public function queueSync(Proxy $object) : void {
		if($object->__getObject()->{$this->config[$object->__getTable()]['primary_property'] ?? 'id'}) {
			$this->unitOfWork->registerUpdate($object);
		}
		else {
			$this->unitOfWork->registerCreate($object);
		}
	}

	public function delete(Proxy $object) : void {
		$this->unitOfWork->registerDelete($object);
	}

	public function link(Proxy $object,Proxy $toObject) : void{
		$this->unitOfWork->registerLink($object,$toObject);
	}

	public function unlink(Proxy $object,Proxy $fromObject) : void {
		$this->unitOfWork->registerUnlink($object,$fromObject);
	}

	public function flush() : void {
		$this->unitOfWork->flush();
	}
}