<?php

namespace AutoTable;

use Zend\Hydrator\HydratorInterface;

class HydratorProxyFactory {
	public function create(HydratorInterface $hydrator) : HydratorProxy {
		return new HydratorProxy($hydrator);
	}
}

