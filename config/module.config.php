<?php
return [
	'auto_tables' => [],
	'service_manager' => [
		'factories' => [
			AutoTable\AutoTableManager::class => AutoTable\AutoTableManagerServiceFactory::class,
		]
	]
];