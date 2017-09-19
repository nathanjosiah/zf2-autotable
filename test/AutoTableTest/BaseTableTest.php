<?php

namespace AutoTableTest;

use AutoTable\BaseTable;
use AutoTable\Proxy;
use AutoTable\AutoTableManager;
use AutoTable\UnitOfWork;
use Zend\Hydrator\ObjectProperty;
use AutoTable\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use AutoTable\HydratorProxy;

class BaseTableTest extends \PHPUnit_Framework_TestCase {
	private $table,$gateway;
	public function setUp() {
		$this->gateway = $this->getMockBuilder(TableGateway::class)->disableOriginalConstructor()->getMock();
		$this->table = new BaseTable();
		$this->table->setTableGateway($this->gateway);
		$this->table->setIdProperty('id');
		$this->table->setPrimaryColumn('id');
		$this->table->setTablesConfig([]);
	}

	public function testFetchWithId() {
		$entity = new \stdClass();
		$this->gateway->expects($this->once())->method('select')->with(['id'=>123])->willReturn(new \ArrayIterator([$entity]));
		$result = $this->table->fetchWithId(123);
		$this->assertSame($entity,$result);
	}

	public function testFetchWithId_customColumn() {
		$entity = new \stdClass();
		$this->table->setPrimaryColumn('foo');
		$this->gateway->expects($this->once())->method('select')->with(['foo'=>123])->willReturn(new \ArrayIterator([$entity]));
		$result = $this->table->fetchWithId(123);
		$this->assertSame($entity,$result);
	}

	public function testFetchWithFilter() {
		$rows = new \ArrayIterator([new \stdClass()]);
		$filter = ['myarray'=>123];
		$this->gateway->expects($this->once())->method('select')->with($filter)->willReturn($rows);
		$result = $this->table->fetchWithFilter($filter);
		$this->assertSame($rows,$result);
	}

	public function testFetchAll() {
		$rows = new \ArrayIterator([new \stdClass()]);
		$this->gateway->expects($this->once())->method('select')->with()->willReturn($rows);
		$result = $this->table->fetchAll();
		$this->assertSame($rows,$result);
	}

	public function testFetchPaginated() {
		if(!class_exists(\Zend\Paginator\Paginator::class,true)) {
			return $this->markTestSkipped('Zend\Paginator not loaded.');
		}
		$this->markTestIncomplete();
	}

	public function testDeleteWithId() {
		$this->gateway->expects($this->once())->method('delete')->with(['id'=>123]);
		$this->table->deleteWithId(123);
	}

	public function testDeleteWithId_customColumn() {
		$this->table->setPrimaryColumn('foo');
		$this->gateway->expects($this->once())->method('delete')->with(['foo'=>123]);
		$this->table->deleteWithId(123);
	}

	public function testSave_newRecord() {
		$entity = new \stdClass();
		$entity->id = null;
		$entity->mydata = 123;
		$config = ['mytable'=>[]];
		$this->table->setTablesConfig($config);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setObject($entity);
		$proxy->__setTable('mytable');

		$result_set = $this->getMockBuilder(HydratingResultSet::class)->disableOriginalConstructor()->getMock();
		$result_set->expects($this->once())->method('getHydrator')->willReturn(new HydratorProxy(new ObjectProperty()));
		$this->gateway->expects($this->once())->method('getResultSetPrototype')->willReturn($result_set);
		$this->gateway->expects($this->once())->method('getLastInsertValue')->willReturn(456);

		$this->gateway->expects($this->once())->method('insert')->with(['mydata'=>123]);
		$this->table->save($proxy);
		$this->assertSame(456,$entity->id);
	}

	public function testSave_newRecord_customPropertyAndColumn() {
		$entity = new \stdClass();
		$entity->id = 'nottheid';
		$entity->foo = null;
		$entity->mydata = 123;
		$config = ['mytable'=>[]];
		$this->table->setTablesConfig($config);
		$this->table->setIdProperty('foo');
		$this->table->setPrimaryColumn('foo');

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setObject($entity);
		$proxy->__setTable('mytable');

		$result_set = $this->getMockBuilder(HydratingResultSet::class)->disableOriginalConstructor()->getMock();
		$result_set->expects($this->once())->method('getHydrator')->willReturn(new HydratorProxy(new ObjectProperty()));
		$this->gateway->expects($this->once())->method('getResultSetPrototype')->willReturn($result_set);
		$this->gateway->expects($this->once())->method('getLastInsertValue')->willReturn(456);

		// This also asserts implicitly that a null id isn't saved
		$this->gateway->expects($this->once())->method('insert')->with(['mydata'=>123,'id'=>'nottheid']);
		$this->table->save($proxy);
		$this->assertSame(456,$entity->foo);
	}

	public function testSave_updateRecord() {
		$entity = new \stdClass();
		$entity->id = 654;
		$entity->mydata = 123;
		$config = ['mytable'=>[]];
		$this->table->setTablesConfig($config);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setObject($entity);
		$proxy->__setTable('mytable');

		$result_set = $this->getMockBuilder(HydratingResultSet::class)->disableOriginalConstructor()->getMock();
		$result_set->expects($this->once())->method('getHydrator')->willReturn(new HydratorProxy(new ObjectProperty()));
		$this->gateway->expects($this->once())->method('getResultSetPrototype')->willReturn($result_set);

		$this->gateway->expects($this->once())->method('update')->with(['id'=>654,'mydata'=>123],['id'=>654]);
		$this->table->save($proxy);
	}

	public function testSave_updateRecord_withCustomPropertyAndColumn() {
		$entity = new \stdClass();
		$entity->id = 'nottheid';
		$entity->foo = 654;
		$entity->mydata = 123;
		$config = ['mytable'=>[]];
		$this->table->setTablesConfig($config);
		$this->table->setIdProperty('foo');
		$this->table->setPrimaryColumn('foo');

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setObject($entity);
		$proxy->__setTable('mytable');

		$result_set = $this->getMockBuilder(HydratingResultSet::class)->disableOriginalConstructor()->getMock();
		$result_set->expects($this->once())->method('getHydrator')->willReturn(new HydratorProxy(new ObjectProperty()));
		$this->gateway->expects($this->once())->method('getResultSetPrototype')->willReturn($result_set);

		$this->gateway->expects($this->once())->method('update')->with(['foo'=>654,'mydata'=>123,'id'=>'nottheid'],['foo'=>654]);
		$this->table->save($proxy);
	}

	public function testExtractData() {
		$entity = new \stdClass();
		$entity->id = 123;
		$entity->mydata = 'data';
		$entity->onetoone = 213;
		$entity->onetomany = 324;
		$entity->onetomany2 = 435;
		$entity->onetomany3 = 546;
		$entity->manytomany = 657;
		$config = ['mytable'=> [
			'linked_tables' => [
				'onetoone' => [
					'type' => 'one_to_one',
				],
				'onetomany' => [
					'type' => 'one_to_many',
				],
				'onetomany2' => [
					'alias_to' => 'foo',
				],
				'onetomany3' => [
					'type' => 'one_to_many',
					'should_save' => true,
				],
				'manytomany' => [
					'type' => 'many_to_many',
				],
			]
		]];
		$this->table->setTablesConfig($config);

		$unit_of_work = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();
		$proxy = new Proxy($manager,$unit_of_work,$config);
		$proxy->__setObject($entity);
		$proxy->__setTable('mytable');

		$result_set = $this->getMockBuilder(HydratingResultSet::class)->disableOriginalConstructor()->getMock();
		$result_set->expects($this->once())->method('getHydrator')->willReturn(new HydratorProxy(new ObjectProperty()));
		$this->gateway->expects($this->once())->method('getResultSetPrototype')->willReturn($result_set);

		$this->gateway->expects($this->once())->method('update')->with([
			'id' => 123,
			'mydata' => 'data',
			'onetoone' => 213,
			'onetomany3' => 546
		],['id'=>123]);
		$this->table->save($proxy);
	}

	public function testFetchWithFilteredJoin() {
		$this->markTestIncomplete();
	}
}

