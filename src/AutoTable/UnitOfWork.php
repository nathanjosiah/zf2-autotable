<?php

namespace AutoTable;

class UnitOfWork {
	const TYPE_CREATE = 'create';
	const TYPE_UPDATE = 'update';
	const TYPE_DELETE = 'delete';
	const TYPE_LINK = 'link';
	const TYPE_UNLINK = 'unlink';

	public $object,$type,$manager,$config;
	private $work = [];

	public function __construct(AutoTableManager $manager,array $config) {
		$this->manager = $manager;
		$this->config = $config;
	}

	private function doWork($object,string $type) {
		if($type === self::TYPE_CREATE || $type === self::TYPE_UPDATE) {
			$this->manager->getTable($object->__getTable())->save($object);
		}
		elseif($type === self::TYPE_DELETE) {
			$id_property = $this->config[$object->__getTable()]['primary_property'] ?? 'id';
			$this->manager->getTable($object->__getTable())->deleteWithId($object->{$id_property});
		}
		elseif($type === self::TYPE_LINK || $type === self::TYPE_UNLINK) {
			$mainObject = $object->object;
			$toObject = $object->link;

			$id_property = $this->config[$mainObject->__getTable()]['primary_property'] ?? 'id';
			$to_id_property = $this->config[$toObject->__getTable()]['primary_property'] ?? 'id';

			$table_config = $this->config[$toObject->__getTable()];
			$linked_tables_config = $table_config['linked_tables'];
			foreach($linked_tables_config as $link_config) {
				if($link_config['type'] === 'many_to_many' && $link_config['remote_table'] === $mainObject->__getTable()) {
					// $link_config now set to the correct config. Stop looking.
					break;
				}
			}

			// Get the main object table name based on the toObject.linked_table[].remote_table = mainObject.__getTable
			$gateway = $this->manager->createTableGateway($this->config[$link_config['mapping_table']]['table_name']);

			if($type === self::TYPE_LINK) {
				$gateway->insert([
					$link_config['local_mapping_column'] => $toObject->{$to_id_property},
					$link_config['remote_mapping_column'] => $mainObject->{$id_property},
				]);
			}
			else {
				$gateway->delete([
					$link_config['local_mapping_column'] => $toObject->{$to_id_property},
					$link_config['remote_mapping_column'] => $mainObject->{$id_property},
				]);
			}
		}
	}

	public function registerCreate($object) : void {
		$this->registerWork($object,self::TYPE_CREATE);
	}

	public function registerUpdate($object) : void {
		$this->registerWork($object,self::TYPE_UPDATE);
	}

	public function registerDelete($object) : void {
		$this->registerWork($object,self::TYPE_DELETE);
	}

	public function registerLink(Proxy $object,Proxy $toObject) : void {
		$this->registerWork((object)['object'=>$object,'link'=>$toObject],self::TYPE_LINK);
	}

	public function registerUnlink(Proxy $object,Proxy $fromObject) : void {
		$this->registerWork((object)['object'=>$object,'link'=>$fromObject],self::TYPE_UNLINK);
	}

	private function registerWork($object,string $type) : void {
		// Create or update would trigger the same behavior for now. Including an update when a create is queued is redundant
		$hash = spl_object_hash($object) . ':' . ($type === self::TYPE_CREATE || $type === self::TYPE_UPDATE ? 'createorupdate' : $type);
		if(isset($this->work[$hash])) {
			return;
		}
		$this->work[$hash] = [$object,$type];
	}

	public function flush() {
		foreach($this->work as [$object,$type]) {
			$this->doWork($object,$type);
		}
		$this->work = [];
	}
}

