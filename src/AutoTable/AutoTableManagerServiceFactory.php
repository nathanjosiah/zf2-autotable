<?php

namespace AutoTable;

use Zend\ServiceManager\FactoryInterface;

class AutoTableManagerServiceFactory implements FactoryInterface {
	/**
	 * ZF3 Migration prep / ZF2 Compatibility
	 */
	public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container) {
		return $this($container,'');
	}

	public function __invoke(\Interop\Container\ContainerInterface $serviceLocator,$requested_name,array $options = null) {
		$table_gateway_factory = new TableGatewayFactory($serviceLocator->get('Zend\Db\Adapter\Adapter'));
		$config = $serviceLocator->get('Config')['auto_tables'];
		$manager = new AutoTableManager(
			$config,
			$serviceLocator,
			new HydratorProxyFactory(),
			new ResultSetFactory(),
			new ProxyFactory(),
			$table_gateway_factory
		);
		$manager->setUnitOfWork(new UnitOfWork($manager,$config,$table_gateway_factory));
		return $manager;
	}
}

