<?php

namespace AutoTableTest;

use AutoTable\UnitOfWork;
use AutoTable\AutoTableManager;
use AutoTable\TableInterface;
use AutoTable\Proxy;
use Zend\Db\TableGateway\TableGateway;
use AutoTable\TableGatewayFactory;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase {

	public function testCreationTriggersSave() {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		$object->expects($this->once())->method('__getTable')->will($this->returnValue('mytable'));

		$table = $this->getMockBuilder(TableInterface::class)->getMock();

		// Assert that the proxy is passed into the table
		$table->expects($this->once())->method('save')->with($object);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$manager->expects($this->once())->method('getTable')->with('mytable')->will($this->returnValue($table));

		$config = [];
		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		$uow->registerCreate($object);
		$uow->flush();
	}

	public function testCreationDoesntDuplicateSaveForSameObjectWhenAddedMultipleTimes() {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		$object->expects($this->once())->method('__getTable')->will($this->returnValue('mytable'));

		$table = $this->getMockBuilder(TableInterface::class)->getMock();

		// Assert that the proxy is passed into the table and only called once
		$table->expects($this->once())->method('save')->with($object);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$manager->expects($this->once())->method('getTable')->with('mytable')->will($this->returnValue($table));

		$config = [];
		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		$uow->registerCreate($object);
		$uow->registerCreate($object);
		$uow->flush();
	}

	public function testCreationDoesntDuplicateSaveForSameObjectWhenUpdatedBeforeFlushed() {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		$object->expects($this->once())->method('__getTable')->will($this->returnValue('mytable'));

		$table = $this->getMockBuilder(TableInterface::class)->getMock();

		// Assert that the proxy is passed into the table and only called once
		$table->expects($this->once())->method('save')->with($object);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$manager->expects($this->once())->method('getTable')->with('mytable')->will($this->returnValue($table));

		$config = [];
		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		$uow->registerCreate($object);
		$uow->registerUpdate($object);
		$uow->flush();
	}

	public function testUpdateTriggersSave() {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		$object->expects($this->once())->method('__getTable')->will($this->returnValue('mytable'));

		$table = $this->getMockBuilder(TableInterface::class)->getMock();

		// Assert that the proxy is passed into the table
		$table->expects($this->once())->method('save')->with($object);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$manager->expects($this->once())->method('getTable')->with('mytable')->will($this->returnValue($table));

		$config = [];
		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		$uow->registerUpdate($object);
		$uow->flush();
	}

	public function testDeletionWithDefaultPrimaryProperty() {
		$this->_testDelete();
	}

	public function testDeletionWithCustomPrimaryProperty() {
		$this->_testDelete(true);
	}

	private function _testDelete($custom_prop=false) {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		// Assert that the id is being retrieved from the correct property
		$object->expects($this->any())->method('__get')->with($custom_prop ? 'myrealid' : 'id')->will($this->returnValue(123));
		$object->expects($this->any())->method('__getTable')->will($this->returnValue('mytable'));

		$table = $this->getMockBuilder(TableInterface::class)->getMock();

		// Assert that the correct id is being used
		$table->expects($this->once())->method('deleteWithId')->with(123);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$manager->expects($this->once())->method('getTable')->with('mytable')->will($this->returnValue($table));

		$config = [
			'mytable' => [
			]
		];
		if($custom_prop) {
			$config['mytable']['primary_property'] = 'myrealid';
		}
		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		$uow->registerDelete($object);
		$uow->flush();
	}


	public function testLinkWithDefaultPrimaryProperty() {
		$this->_testLink();
	}

	public function testLinkWithCustomPrimaryProperty() {
		$this->_testLink(false,true);
	}

	public function testUnlinkWithDefaultPrimaryProperty() {
		$this->_testLink(true,false);
	}

	public function testUnlinkWithCustomPrimaryProperty() {
		$this->_testLink(true,true);
	}

	private function _testLink($unlink=false,$custom_prop=false) {
		$table_gateway_factory = $this->getMockBuilder(TableGatewayFactory::class)->disableOriginalConstructor()->getMock();
		$object = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		// Assert that the id is being retrieved from the correct property
		$object->expects($this->any())->method('__get')->with($custom_prop ? 'myrealid' : 'id')->will($this->returnValue(123));
		$object->expects($this->any())->method('__getTable')->will($this->returnValue('mytable'));

		$object2 = $this->getMockBuilder(Proxy::class)->disableOriginalConstructor()->getMock();
		// Assert that the id is being retrieved from the correct property
		$object2->expects($this->any())->method('__get')->with($custom_prop ? 'myrealid2' : 'id')->will($this->returnValue(432));
		$object2->expects($this->any())->method('__getTable')->will($this->returnValue('mytable2'));

		$gateway = $this->getMockBuilder(TableGateway::class)->disableOriginalConstructor()->getMock();
		// Assert that the correct data is saved
		$gateway->expects($this->once())->method($unlink ? 'delete' : 'insert')->with(['lmc'=>432,'rmc'=>123]);

		$manager = $this->getMockBuilder(AutoTableManager::class)->disableOriginalConstructor()->getMock();

		// Assert that the correct table is retrieved
		$table_gateway_factory->expects($this->once())->method('create')->with('super_map')->will($this->returnValue($gateway));

		$config = [
			'mytable' => [
			],
			'mytable2' => [
				'linked_tables' => [
					'bad_skip_me' => [
						'type' => 'one_to_one'
					],
					'bad_skip_me_too' => [
						'type' => 'one_to_many'
					],
					'im_the_winner' => [
						'type' => 'many_to_many',
						'mapping_table' => 'my_mapping_table',
						'local_mapping_column' => 'lmc',
						'remote_mapping_column' => 'rmc',
						'remote_table' => 'mytable',
					],
				]
			],
			'my_mapping_table' => [
				'table_name' => 'super_map'
			]
		];


		if($custom_prop) {
			$config['mytable']['primary_property'] = 'myrealid';
			$config['mytable2']['primary_property'] = 'myrealid2';
		}

		$uow = new UnitOfWork($manager,$config,$table_gateway_factory);
		if($unlink) {
			$uow->registerUnlink($object,$object2);
		}
		else {
			$uow->registerLink($object,$object2);
		}
		$uow->flush();
	}
}

