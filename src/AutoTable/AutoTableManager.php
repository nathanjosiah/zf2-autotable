<?php

namespace AutoTable;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;

class AutoTableManager {
	protected $config,$serviceLocator;
	protected $tableCache = [];

	public function __construct(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		$this->config = $serviceLocator->get('Config')['auto_tables'];
	}

	public function getTable(string $name) {
		// Waste not
		if(isset($this->tableCache[$name])) {
			return $this->tableCache[$name];
		}

		$table_config = $this->config[$name];
		$entity_class = $table_config['entity'];

		$adapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
		$hydrator = new HydratorProxy($this->serviceLocator->get($table_config['hydrator']));
		$proxy = new Proxy($this,$this->config);
		$entity = ($this->serviceLocator->has($entity_class) ? $this->serviceLocator->get($entity_class) : new $entity_class());
		$result_set = new HydratingResultSet($hydrator,$entity,$proxy,$name);
		$gateway = new TableGateway($table_config['table_name'],$adapter,[],$result_set);
		$table = new BaseTable($gateway,$table_config['id_column'] ?? 'id');

		$this->tableCache[$name] = $table;
		return $table;
	}
}