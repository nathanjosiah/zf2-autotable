<?php

namespace AutoTable;

class UnitOfWorkFactory {
	public function create(AutoTableManager $manager, array $config,TableGatewayFactory $table_gateway_factory) : UnitOfWork {
		return new UnitOfWork($manager,$config,$table_gateway_factory);
	}
}

