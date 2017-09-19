<?php

namespace AutoTableTest;

use AutoTable\HydratingResultSet;
use Zend\Hydrator\ObjectProperty;
use AutoTable\Proxy;
use AutoTable\ProxyFactory;

class HydratingResultSetTest extends \PHPUnit_Framework_TestCase {

	public function testCurrentReturnsNullForResultsThatAreNotObjects() {
		$entity = new \stdClass();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$result_set = new HydratingResultSet(new ObjectProperty(),$entity,$proxy_factory,'foobar');
		$result_set->initialize([]);
		$result = $result_set->current();
		$this->assertFalse($result);
	}

	public function testResultIsConvertedToProxy() {
		$entity = new \stdClass();
		$entity->data = null;


		$entity_hydrated = new \stdClass();
		$entity_hydrated->data = 123;

		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$proxy = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();

		$proxy_factory->expects($this->once())->method('create')->willReturn($proxy);
		$proxy->expects($this->once())->method('__setObject')->with($entity_hydrated);
		$proxy->expects($this->once())->method('__setTable')->with('foobar');

		$result_set = new HydratingResultSet(new ObjectProperty(),$entity,$proxy_factory,'foobar');
		$result_set->initialize(new \ArrayIterator([['data'=>123]]));

		$ran = false;
		foreach($result_set as $row) {
			if($ran) {
				$this->fail('Should not have more than one row.');
			}
			$this->assertSame($proxy,$row);
			$ran = true;
		}
		if(!$ran) {
			$this->fail('Should have at least one row.');
		}
	}
}

