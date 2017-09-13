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
		$manager = new AutoTableManager($serviceLocator);
		return $manager;
	}
}

