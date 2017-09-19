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
	protected $serviceLocator,$unitOfWork,$hydratorProxyFactory,$resultSetFactory,$proxyFactory,$tableGatewayFactory,$manager;

	public function setUp() {
		$this->serviceLocator = new ServiceManager();
		$this->unitOfWork = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$this->hydratorProxyFactory = $this->getMockBuilder(HydratorProxyFactory::class)->getMock();
		$this->resultSetFactory = $this->getMockBuilder(ResultSetFactory::class)->getMock();
		$this->proxyFactory = $this->getMockBuilder(ProxyFactory::class)->disableOriginalConstructor()->getMock();
		$this->tableGatewayFactory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$this->manager = new AutoTableManager([],$this->serviceLocator,$this->hydratorProxyFactory,$this->resultSetFactory,$this->proxyFactory,$this->tableGatewayFactory);
		$this->manager->setUnitOfWork($this->unitOfWork);
	}

	public function testGetTableConfiguresTableCorrectly() {
		$config = [
			'mytable' => [
				'table_name' => 'real_table',
				'entity' => 'myentity',
				'hydrator' => 'myhydrator',
			]
		];
		$this->manager->setConfig($config);
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();
		$this->serviceLocator->setService('AutoTable\BaseTable',$mytable);
		$this->serviceLocator->setService('myhydrator',$hydrator);
		$this->serviceLocator->setService('myentity',$entity);

		// Stubs for the assertions
		$hydrator_proxy = $this->getMockBuilder(HydratorProxy::class)->disableOriginalConstructor()->getMock();
		$result_set = $this->getMockBuilder(ResultSetInterface::class)->getMock();
		$table_gateway = $this->getMockBuilder(TableGatewayInterface::class)->getMock();


		// Assert the hydrator is proxied correctly
		$this->hydratorProxyFactory->expects($this->once())->method('create')->with($hydrator)->willReturn($hydrator_proxy);
		// Assert the result set is created correctly
		$this->resultSetFactory->expects($this->once())->method('create')->with($hydrator_proxy,$entity,$this->proxyFactory,'mytable')->willReturn($result_set);
		// Assert the result set is created correctly
		$this->tableGatewayFactory->expects($this->once())->method('create')->with('real_table',[],$result_set)->willReturn($table_gateway);
		// Assert the able gets the gateway injected
		$mytable->expects($this->once())->method('setTableGateway')->with($table_gateway);

		$table = $this->manager->getTable('mytable');
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
		$this->manager->setConfig($config);
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();

		$this->serviceLocator->setService('AutoTable\BaseTable',$mytable);
		$this->serviceLocator->setService('myhydrator',$hydrator);
		$this->serviceLocator->setService('myentity',$entity);

		$table = $this->manager->getTable('mytable');
		// Make sure the table is returned
		$this->assertSame($table,$mytable);

		// Make sure we get the exact same table if called again with same name.
		$table = $this->manager->getTable('mytable');
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
		$this->manager->setConfig($config);
		$mytable = $this->getMockBuilder(TableInterface::class)->getMock();
		$hydrator = $this->getMockBuilder(HydratorInterface::class)->getMock();
		$entity = new \stdClass();

		$this->serviceLocator->setService('mysupertable',$mytable);
		$this->serviceLocator->setService('myhydrator',$hydrator);
		$this->serviceLocator->setService('myentity',$entity);

		// Assert the able gets the gateway injected
		$mytable->expects($this->once())->method('setPrimaryColumn')->with('myid');
		$mytable->expects($this->once())->method('setIdProperty')->with('myprop');

		$table = $this->manager->getTable('mytable');
		// Make sure the table is returned
		$this->assertSame($table,$mytable);

		// Make sure we get the exact same table if called again with same name.
		$table = $this->manager->getTable('mytable');
		$this->assertSame($table,$mytable);
	}


	public function testCreateNew() {
		$config = [
			'mytable' => [
				'entity' => 'myentity',
			]
		];
		$this->manager->setConfig($config);
		$entity = new \stdClass();
		$this->serviceLocator->setService('myentity',$entity);

		$proxy = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();

		// Assert proxy is created correctly
		$this->proxyFactory->expects($this->once())->method('create')->willReturn($proxy);
		// Assert entity is assigned
		$proxy->expects($this->once())->method('__setObject')->with($entity);
		// Assert table name is assigned
		$proxy->expects($this->once())->method('__setTable')->with('mytable');
		// Assert proxy is registered
		$this->unitOfWork->expects($this->once())->method('registerCreate')->with($proxy);

		$result = $this->manager->createNew('mytable');
		// Make sure the proxy is returned
		$this->assertSame($result,$proxy);
	}

	public function testTrack() {
		$config = [];
		$entity = new \stdClass();
		$proxy = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();

		// Assert proxy is created correctly
		$this->proxyFactory->expects($this->once())->method('create')->willReturn($proxy);
		// Assert entity is assigned
		$proxy->expects($this->once())->method('__setObject')->with($entity);
		// Assert table name is assigned
		$proxy->expects($this->once())->method('__setTable')->with('mytable');

		$result = $this->manager->track($entity,'mytable');
		// Make sure the proxy is returned
		$this->assertSame($result,$proxy);
	}

	public function testQueueSync_ForExistingObject() {
		$config = [
			'mytable' => [
				'primary_property' => 'foo'
			]
		];
		$this->manager->setConfig($config);

		$entity = new \stdClass();
		$entity->foo = '123';
		$proxy = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is registered as an update
		$this->unitOfWork->expects($this->once())->method('registerUpdate')->with($proxy);

		$this->manager->queueSync($proxy);
	}

	public function testQueueSync_ForNewObject() {
		$config = [
			'mytable' => [
				'primary_property' => 'foo'
			]
		];
		$this->manager->setConfig($config);

		$entity = new \stdClass();
		$entity->foo = null;
		$proxy = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is registered as an update
		$this->unitOfWork->expects($this->once())->method('registerCreate')->with($proxy);

		$this->manager->queueSync($proxy);
	}

	public function testDelete() {
		$config = [];

		$entity = new \stdClass();
		$proxy = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		// Assert the proxy is deleted
		$this->unitOfWork->expects($this->once())->method('registerDelete')->with($proxy);

		$this->manager->delete($proxy);
	}

	public function testLink() {
		$config = [];

		$entity = new \stdClass();
		$proxy = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		$entity2 = new \stdClass();
		$proxy2 = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy2->__setTable('mytable');
		$proxy2->__setObject($entity);

		// Assert the proxy is deleted
		$this->unitOfWork->expects($this->once())->method('registerLink')->with($proxy,$proxy2);

		$this->manager->link($proxy,$proxy2);
	}

	public function testUnlink() {
		$config = [];

		$entity = new \stdClass();
		$proxy = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy->__setTable('mytable');
		$proxy->__setObject($entity);

		$entity2 = new \stdClass();
		$proxy2 = new Proxy($this->manager,$this->unitOfWork,$config);
		$proxy2->__setTable('mytable');
		$proxy2->__setObject($entity);

		// Assert the proxy is deleted
		$this->unitOfWork->expects($this->once())->method('registerUnlink')->with($proxy,$proxy2);

		$this->manager->unlink($proxy,$proxy2);
	}

	public function testFlush() {
		$config = [];

		// Assert flush is proxied
		$this->unitOfWork->expects($this->once())->method('flush');

		$this->manager->flush();
	}
}

