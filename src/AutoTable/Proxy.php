<?php
namespace AutoTable;

class Proxy {
	private $__object,$__manager,$__tablesConfig,$__table;

	public function __construct(AutoTableManager $manager,array $tables_config) {
		$this->__manager = $manager;
		$this->__tablesConfig = $tables_config;
	}

	public function __getObject() {
		return $this->__object;
	}

	public function __getTable() {
		return $this->__table;
	}

	public function __setObject($object) : void {
		$this->__object = $object;
	}

	public function __setTable(string $table) : void {
		$this->__table = $table;
	}

	public function __get($name) {
		if(empty($this->__tablesConfig[$this->__table]['linked_tables'][$name])) {
			return $this->__object->{$name};
		}

		$table_config = $this->__tablesConfig[$this->__table];
		$linked_tables_config = $table_config['linked_tables'];
		if(!empty($linked_tables_config[$name]['alias_to']) && !empty($linked_tables_config[$linked_tables_config[$name]['alias_to']])) {
			$name = $linked_tables_config[$name]['alias_to'];
		}

		$link_config = $linked_tables_config[$name];

		$type = $link_config['type'];

		if($type === 'one_to_one') {
			$table_name = $link_config['name'];
			$table = $this->__manager->getTable($table_name);
			$result = $table->fetchWithId($this->__object->{$name});
		}
		elseif($type === 'one_to_many') {
			$table_name = $link_config['name'];
			$table = $this->__manager->getTable($table_name);
			$result = $table->fetchWithFilter([$link_config['remote_column'] => $this->__object->{$link_config['local_property']}]);
		}
		elseif($type === 'many_to_many') {
			$table = $this->__manager->getTable($link_config['remote_table']);
			$mapping_table_name = $this->__tablesConfig[$link_config['mapping_table']]['table_name'];
			$result = $table->fetchWithFilteredJoin(
				[$mapping_table_name.'.'.$link_config['local_mapping_column']=>$this->__object->{$link_config['local_property']}],
				$table_config['table_name'],
				$mapping_table_name,
				$link_config['local_column'],
				$link_config['local_mapping_column'],
				$link_config['remote_mapping_column'],
				$link_config['remote_column']
			);
		}

		return $result;
	}

	public function __set($name,$value) {
		$work = UnitOfWork::create($this,UnitOfWork::TYPE_UPDATE);
		$this->__manager->registerWork($work);

		$table_config = $this->__tablesConfig[$this->__table];

		$original_name = $name;

		// Resovle any aliases
		if(!empty($table_config['linked_tables'])) {
			$linked_tables_config = $table_config['linked_tables'];
			if(!empty($linked_tables_config[$name]['alias_to']) && !empty($linked_tables_config[$linked_tables_config[$name]['alias_to']])) {
				$name = $linked_tables_config[$name]['alias_to'];
			}
		}

		if(!is_object($value) || empty($this->__tablesConfig[$this->__table]['linked_tables'][$original_name])) {
			$this->__object->{$name} = $value;
		}
		else {
			// We can only process Proxy's
			if(!$value instanceof Proxy) {
				throw new \Exception('Invalid object type assigned to AutoTable linked property.');
			}
			$link_config = $linked_tables_config[$name];
			// The assigned value is a Proxy but it's the wrong Proxy
			if($value->__getTable() !== $link_config['name']) {
				throw new \Exception('Invalid AutoTable object type assigned to AutoTable linked property.');
			}
			$linked_table_config = $this->__tablesConfig[$link_config['name']];

			$this->__object->{$name} = $value->{$linked_table_config['primary_property'] ?? 'id'};
		}
	}
}