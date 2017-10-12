<?php

namespace AutoTable;

class ProxyFactory {
	protected  $manager,$unitOfWork,$tablesConfig;
	public function __construct(UnitOfWork $unitOfWork=null,array $tables_config=[],AutoTableManager $manager=null) {
		$this->manager = $manager;
		$this->unitOfWork = $manager;
		$this->tablesConfig = $tables_config;
	}
	public function create() : Proxy {
		return new Proxy($this->manager,$this->unitOfWork,$this->tablesConfig);
	}

	public function setManager(AutoTableManager $manager) : void {
		$this->manager = $manager;
	}

	public function setUnitOfWork(UnitOfWork $unitOfWork) : void {
		$this->unitOfWork = $unitOfWork;
	}
}

