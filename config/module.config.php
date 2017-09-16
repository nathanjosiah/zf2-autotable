<?php
return [
	'auto_tables' => [
	],
	'service_manager' => [
		'factories' => [
			AutoTable\BaseTable::class => Zend\ServiceManager\Factory\InvokableFactory::class,
			AutoTable\AutoTableManager::class => AutoTable\AutoTableManagerServiceFactory::class,
		],
		'shared' => [
			AutoTable\BaseTable::class => false,
		]
	]
];