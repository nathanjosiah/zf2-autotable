<?php

namespace AutoTable;

class ProxyFactory {
	public function create(AutoTableManager $manager,UnitOfWork $unitOfWork,array $tables_config) : Proxy {
		return new Proxy($manager,$unitOfWork,$tables_config);
	}
}

