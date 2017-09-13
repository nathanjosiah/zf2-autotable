<?php

namespace AutoTable;

use Zend\Hydrator\HydratorInterface;

class HydratorProxy implements HydratorInterface {
	private $instance;

	public function __construct(HydratorInterface $instance) {
		$this->instance = $instance;
	}

	public function extract($object) {
		if(!$object instanceof Proxy) {
			return $this->instance->extract($object);
		}
		return $this->instance->extract($object->__getObject());
	}
	public function hydrate(array $data,$object) {
		if(!$object instanceof Proxy) {
			return $this->instance->hydrate($data,$object);
		}
		return $this->instance->hydrate($data,$object->__getObject());
	}
}

