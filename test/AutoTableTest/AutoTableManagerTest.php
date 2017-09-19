<?php

namespace AutoTableTest;

use AutoTable\HydratorProxyFactory;
use AutoTable\ResultSetFactory;
use AutoTable\ProxyFactory;
use AutoTable\TableGatewayFactory;
use AutoTable\AutoTableManager;
use AutoTable\UnitOfWork;
use AutoTable\TableInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Hydrator\HydratorInterface;
use AutoTable\HydratorProxy;
use AutoTable\Proxy;
use Zend\Db\TableGateway\TableGatewayInterface;

class AutoTableManagerTest extends \PHPUnit_Framework_TestCase {
	public function testGetTableConfiguresTableCorrectly() {
		$config = [
			'mytable' => [
				'table_name' => 'real_table',
				'entity' => 'myentity',
				'hydrator' => 'myhydrator',
			]
		];
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();

		$service_locator = new ServiceManager();
		$service_locator->setService('AutoTable\BaseTable',$mytable);
		$service_locator->setService('myhydrator',$hydrator);
		$service_locator->setService('myentity',$entity);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);


		// Stubs for the assertions
		$hydrator_proxy = $this->getMockBuilder(HydratorProxy::class)->disableOriginalConstructor()->getMock();
		$result_set = $this->getMockBuilder(ResultSetInterface::class)->getMock();
		$table_gateway = $this->getMockBuilder(TableGatewayInterface::class)->getMock();


		// Assert the hydrator is proxied correctly
		$hydrator_proxy_factory->expects($this->once())->method('create')->with($hydrator)->willReturn($hydrator_proxy);
		// Assert the result set is created correctly
		$result_set_factory->expects($this->once())->method('create')->with($hydrator_proxy,$entity,$proxy_factory,'mytable')->willReturn($result_set);
		// Assert the result set is created correctly
		$table_gateway_factory->expects($this->once())->method('create')->with('real_table',[],$result_set)->willReturn($table_gateway);
		// Assert the able gets the gateway injected
		$mytable->expects($this->once())->method('setTableGateway')->with($table_gateway);

		$table = $manager->getTable('mytable');
		// Make sure the table is returned
		$this->assertSame($table,$mytable);
	}

	public function testGetTableReturnsSameInstanceOnSubsequentCalls() {
		$config = [
			'mytable' => [
				'table_name' => 'real_table',
				'entity' => 'myentity',
				'hydrator' => 'myhydrator',
			]
		];
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();

		$service_locator = new ServiceManager();
		$service_locator->setService('AutoTable\BaseTable',$mytable);
		$service_locator->setService('myhydrator',$hydrator);
		$service_locator->setService('myentity',$entity);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$table = $manager->getTable('mytable');
		// Make sure the table is returned
		$this->assertSame($table,$mytable);

		// Make sure we get the exact same table if called again with same name.
		$table = $manager->getTable('mytable');
		$this->assertSame($table,$mytable);
	}

	public function testGetTableUsesCustomValues() {
		$config = [
			'mytable' => [
				'table_name' => 'real_table',
				'entity' => 'myentity',
				'hydrator' => 'myhydrator',
				'table' => 'mysupertable',
				'primary_property' => 'myprop',
				'id_column' => 'myid',
			]
		];
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();

		$service_locator = new ServiceManager();
		$service_locator->setService('mysupertable',$mytable);
		$service_locator->setService('myhydrator',$hydrator);
		$service_locator->setService('myentity',$entity);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);


		// Assert the able gets the gateway injected
		$mytable->expects($this->once())->method('setPrimaryColumn')->with('myid');
		$mytable->expects($this->once())->method('setIdProperty')->with('myprop');

		$table = $manager->getTable('mytable');
		// Make sure the table is returned
		$this->assertSame($table,$mytable);

		// Make sure we get the exact same table if called again with same name.
		$table = $manager->getTable('mytable');
		$this->assertSame($table,$mytable);
	}


	public function testCreateNew() {
		$config = [
			'mytable' => [
				'entity' => 'myentity',
			]
		];
		$entity = new \stdClass();
		$service_locator = new ServiceManager();
		$service_locator->setService('myentity',$entity);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$proxy = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();

		// Assert proxy is created correctly
		$proxy_factory->expects($this->once())->method('create')->willReturn($proxy);
		// Assert entity is assigned
		$proxy->expects($this->once())->method('__setObject')->with($entity);
		// Assert table name is assigned
		$proxy->expects($this->once())->method('__setTable')->with('mytable');
		// Assert proxy is registered
		$unit_of_work->expects($this->once())->method('registerCreate')->with($proxy);

		$result = $manager->createNew('mytable');
		// Make sure the proxy is returned
		$this->assertSame($result,$proxy);
	}

	public function testTrack() {
		$config = [];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$proxy = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();

		// Assert proxy is created correctly
		$proxy_factory->expects($this->once())->method('create')->willReturn($proxy);
		// Assert entity is assigned
		$proxy->expects($this->once())->method('__setObject')->with($entity);
		// Assert table name is assigned
		$proxy->expects($this->once())->method('__setTable')->with('mytable');

		$result = $manager->track($entity,'mytable');
		// Make sure the proxy is returned
		$this->assertSame($result,$proxy);
	}

	public function testQueueSync_ForExistingObject() {
		$config = [
			'mytable' => [
				'primary_property' => 'foo'
			]
		];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$entity->foo = '123';
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is registered as an update
		$unit_of_work->expects($this->once())->method('registerUpdate')->with($proxy);

		$manager->queueSync($proxy);
	}

	public function testQueueSync_ForNewObject() {
		$config = [
			'mytable' => [
				'primary_property' => 'foo'
			]
		];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$entity->foo = null;
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is registered as an update
		$unit_of_work->expects($this->once())->method('registerCreate')->with($proxy);

		$manager->queueSync($proxy);
	}

	public function testDelete() {
		$config = [];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is deleted
		$unit_of_work->expects($this->once())->method('registerDelete')->with($proxy);

		$manager->delete($proxy);
	}

	public function testLink() {
		$config = [];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		$entity2 = new \stdClass();
		$proxy2 = new Proxy($manager,$unit_of_work,$config);
		$proxy2->__setTable('mytable');
		$proxy2->__setObject($entity);

		// Assert the proxy is deleted
		$unit_of_work->expects($this->once())->method('registerLink')->with($proxy,$proxy2);

		$manager->link($proxy,$proxy2);
	}

	public function testUnlink() {
		$config = [];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		$entity = new \stdClass();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		$entity2 = new \stdClass();
		$proxy2 = new Proxy($manager,$unit_of_work,$config);
		$proxy2->__setTable('mytable');
		$proxy2->__setObject($entity);

		// Assert the proxy is deleted
		$unit_of_work->expects($this->once())->method('registerUnlink')->with($proxy,$proxy2);

		$manager->unlink($proxy,$proxy2);
	}

	public function testFlush() {
		$config = [];
		$service_locator = new ServiceManager();
		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$hydrator_proxy_factory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$result_set_factory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$proxy_factory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$manager = new AutoTableManager($config,$service_locator,$hydrator_proxy_factory,$result_set_factory,$proxy_factory,$table_gateway_factory);
		$manager->setUnitOfWork($unit_of_work);

		// Assert flush is proxied
		$unit_of_work->expects($this->once())->method('flush');

		$manager->flush();
	}
}

