<?php
namespace AutoTable;

class Proxy {
	private $object,$manager,$tablesConfig,$table;
	public function __construct(AutoTableManager $manager,array $tables_config) {
		$this->manager = $manager;
		$this->tablesConfig = $tables_config;
	}

	public function __getObject() {
		return $this->object;
	}

	public function __setObject($object) : void {
		$this->object = $object;
	}

	public function __setTable(string $table) : void {
		$this->table = $table;
	}

	public function __get($name) {
		if(empty($this->tablesConfig[$this->table]['linked_tables'][$name])) {
			return $this->object->{$name};
		}

		$table_config = $this->tablesConfig[$this->table];
		$linked_tables_config = $table_config['linked_tables'];
		if(!empty($linked_tables_config[$name]['alias_to']) && !empty($linked_tables_config[$linked_tables_config[$name]['alias_to']])) {
			$name = $linked_tables_config[$name]['alias_to'];
		}

		$link_config = $linked_tables_config[$name];


		$type = $link_config['type'] ?? 'one_to_one';

		if($type === 'one_to_one') {
			$table_name = $link_config['name'];
			$table = $this->manager->getTable($table_name);
			$result = $table->fetchWithId($this->object->{$name});
		}
		elseif($type === 'one_to_many') {
			$table_name = $link_config['name'];
			$table = $this->manager->getTable($table_name);
			$result = $table->fetchWithFilter([$link_config['remote_column'] => $this->object->{$link_config['local_property']}]);
		}
		elseif($type === 'many_to_many') {
			$table = $this->manager->getTable($link_config['remote_table']);
			$mapping_table_name = $this->tablesConfig[$link_config['mapping_table']]['table_name'];
			$result = $table->fetchWithFilteredJoin([$mapping_table_name.'.'.$link_config['local_mapping_column']=>$this->object->{$link_config['local_property']}],$this->table,$mapping_table_name,$link_config['local_column'],$link_config['local_mapping_column'],$link_config['remote_mapping_column'],$link_config['remote_column']);
		}

		return $result;
	}
}