<?php

namespace AutoTable;

use Zend\ServiceManager\ServiceLocatorInterface;

class AutoTableManager {
	protected $config,$serviceLocator,$proxyPrototype,$unitOfWork,$hydratorProxyFactory,$resultSetFactory,$proxyFactory,$tableGatewayFactory;
	protected $tableCache = [];
	protected $work = [];

	public function __construct(
		array $config,
		ServiceLocatorInterface $service_locator,
		HydratorProxyFactory $hydrator_proxy_factory,
		ResultSetFactory $result_set_factory,
		ProxyFactory $proxy_factory,
		TableGatewayFactory $table_gateway_factory
	) {
		$this->serviceLocator = $service_locator;
		$this->config = $config;
		$this->hydratorProxyFactory = $hydrator_proxy_factory;
		$this->resultSetFactory = $result_set_factory;
		$this->proxyFactory = $proxy_factory;
		$this->tableGatewayFactory = $table_gateway_factory;
	}

	public function setUnitOfWork(UnitOfWork $unit_of_work) : void {
		$this->unitOfWork = $unit_of_work;
	}

	public function getTable(string $name) {
		// Waste not
		if(isset($this->tableCache[$name])) {
			return $this->tableCache[$name];
		}

		$table_config = $this->config[$name];
		$table_config['id_column'] = $table_config['id_column'] ?? 'id';
		$table_config['primary_property'] = $table_config['primary_property'] ?? 'id';

		$this->proxyPrototype = $this->proxyFactory->create($this,$this->unitOfWork,$this->config);
		$result_set = $this->resultSetFactory->create(
			$this->hydratorProxyFactory->create($this->serviceLocator->get($table_config['hydrator'])),
			$this->getEntityForTable($name),
			$this->proxyPrototype,
			$name
		);
		$gateway = $this->tableGatewayFactory->create($table_config['table_name'],[],$result_set);

		/* @var $table \AutoTable\TableInterface */
		$table = $this->serviceLocator->get($table_config['table'] ?? \AutoTable\BaseTable::class);
		$table->setTableGateway($gateway);
		$table->setPrimaryColumn($table_config['id_column']);
		$table->setIdProperty($table_config['primary_property']);
		$table->setTablesConfig($this->config);

		$this->tableCache[$name] = $table;
		return $table;
	}

	private function getEntityForTable(string $table) {
		$table_config = $this->config[$table];
		$entity_class = $table_config['entity'];
		$entity = ($this->serviceLocator->has($entity_class) ? $this->serviceLocator->get($entity_class) : new $entity_class());
		return $entity;
	}

	public function createNew(string $table) : Proxy {
		$entity = $this->getEntityForTable($table);
		$proxy = $this->proxyFactory->create($this,$this->unitOfWork,$this->config);
		$proxy->__setObject($entity);
		$proxy->__setTable($table);
		$this->unitOfWork->registerCreate($proxy);
		return $proxy;
	}

	public function track($object,string $table) : Proxy {
		$proxy = $this->proxyFactory->create($this,$this->unitOfWork,$this->config);
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