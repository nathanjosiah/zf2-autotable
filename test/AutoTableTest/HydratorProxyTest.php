<?php

namespace AutoTableTest;

use AutoTable\HydratorProxy;
use Zend\Hydrator\HydratorInterface;
use AutoTable\Proxy;

class HydratorProxyTest extends \PHPUnit_Framework_TestCase {
	public function testObjectProxyForProxyObjects() {
		$real_object = new \stdClass();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		$object->expects($this->any())->method('__getObject')->will($this->returnValue($real_object));
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();

		// Assert the correct data is passed through
		$hydrator->expects($this->once())->method('hydrate')->with(['abc'=>123],$real_object)->will($this->returnValue($real_object));
		$hydrator->expects($this->once())->method('extract')->with($real_object)->will($this->returnValue(['abc'=>123]));

		$hydrator_proxy = new HydratorProxy($hydrator);

		// Assert the correct data is returned
		$result = $hydrator_proxy->extract($object);
		$this->assertSame(['abc'=>123],$result);

		// Assert the correct data is returned
		$result = $hydrator_proxy->hydrate(['abc'=>123],$object);
		$this->assertSame($object,$result);
	}

	public function testPassthroughForNonProxyObjects() {
		$object = new \stdClass();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();

		// Assert the correct data is passed through
		$hydrator->expects($this->once())->method('hydrate')->with(['abc'=>123],$object)->will($this->returnValue($object));
		$hydrator->expects($this->once())->method('extract')->with($object)->will($this->returnValue(['abc'=>123]));

		$hydrator_proxy = new HydratorProxy($hydrator);

		// Assert the correct data is returned
		$result = $hydrator_proxy->extract($object);
		$this->assertSame(['abc'=>123],$result);

		// Assert the correct data is returned
		$result = $hydrator_proxy->hydrate(['abc'=>123],$object);
		$this->assertSame($object,$result);
	}
}

