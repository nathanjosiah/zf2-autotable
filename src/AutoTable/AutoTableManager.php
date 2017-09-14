<?php

namespace AutoTable;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSetInterface;

class AutoTableManager {
	protected $config,$serviceLocator;
	protected $tableCache = [];
	protected $work = [];
	protected $proxyPrototype;

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
		$table_config['id_column'] = $table_config['id_column'] ?? 'id';
		$table_config['id_property'] = $table_config['id_property'] ?? 'id';

		$entity_class = $table_config['entity'];

		$hydrator = new HydratorProxy($this->serviceLocator->get($table_config['hydrator']));
		$proxy = new Proxy($this,$this->config);
		$this->proxyPrototype = $proxy;
		$entity = $this->getEntityForTable($name);
		$result_set = new HydratingResultSet($hydrator,$entity,$proxy,$name);
		$gateway = $this->createTableGateway($table_config['table_name'],$result_set);
		$table = new BaseTable($gateway,$table_config['id_column'],$table_config['id_property'],$this->config);

		$this->tableCache[$name] = $table;
		return $table;
	}

	private function createTableGateway(string $table,ResultSetInterface $result_set=null) {
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

	public function createNew(string $table) {
		$entity = $this->getEntityForTable($table);
		$proxy = clone $this->proxyPrototype;
		$proxy->__setObject($entity);
		$proxy->__setTable($table);
		$work = UnitOfWork::create($proxy,UnitOfWork::TYPE_CREATE);
		$this->registerWork($work);
		return $proxy;
	}

	public function delete(Proxy $object) : void {
		$work = UnitOfWork::create($object,UnitOfWork::TYPE_DELETE);
		$this->registerWork($work);
	}

	public function link(Proxy $object,Proxy $toObject) {
		$work = UnitOfWork::create((object)['object'=>$object,'link'=>$toObject],UnitOfWork::TYPE_LINK);
		$this->registerWork($work);
	}

	public function unlink(Proxy $object,Proxy $fromObject) {
		$work = UnitOfWork::create((object)['object'=>$object,'link'=>$fromObject],UnitOfWork::TYPE_UNLINK);
		$this->registerWork($work);
	}

	public function registerWork(UnitOfWork $work) : void {
		$hash = spl_object_hash($work->object) . ':' . $work->type;
		if(isset($this->work[$hash])) {
			return;
		}
		$this->work[$hash] = $work;
	}

	public function flush() : void {
		foreach($this->work as $hash => $work) {
			if($work->type === UnitOfWork::TYPE_CREATE || $work->type === UnitOfWork::TYPE_UPDATE) {
				$this->getTable($work->object->__getTable())->save($work->object);
			}
			elseif($work->type === UnitOfWork::TYPE_DELETE) {
				$id_property = $this->config[$work->object->__getTable()]['primary_property'] ?? 'id';
				$this->getTable($work->object->__getTable())->deleteWithId($work->object->{$id_property});
			}
			elseif($work->type === UnitOfWork::TYPE_LINK || $work->type === UnitOfWork::TYPE_UNLINK) {
				$object = $work->object->object;
				$toObject = $work->object->link;

				$id_property = $this->config[$object->__getTable()]['primary_property'] ?? 'id';
				$to_id_property = $this->config[$toObject->__getTable()]['primary_property'] ?? 'id';

				$table_config = $this->config[$toObject->__getTable()];
				$linked_tables_config = $table_config['linked_tables'];
				foreach($linked_tables_config as $link_config) {
					if($link_config['type'] === 'many_to_many' && $link_config['remote_table'] === $object->__getTable()) {
						break;
					}
				}

				$gateway = $this->createTableGateway($this->config[$link_config['mapping_table']]['table_name']);

				if($work->type === UnitOfWork::TYPE_LINK) {
					$gateway->insert([
						$link_config['local_mapping_column'] => $toObject->{$to_id_property},
						$link_config['remote_mapping_column'] => $object->{$id_property},
					]);
				}
				else {
					$gateway->delete([
						$link_config['local_mapping_column'] => $toObject->{$to_id_property},
						$link_config['remote_mapping_column'] => $object->{$id_property},
					]);
				}
			}
		}
		$this->work = [];
	}
}