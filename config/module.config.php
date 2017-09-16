<?php
return [
	'auto_tables' => [
		'articles' => [
			'table_name' => 'auto_table_articles',
			'entity' => Application\Model\Article::class,
			'hydrator' => Application\Model\Hydrator\ObjectPropertyHydrator::class,
			'table' => Application\Model\Custom::class,
			'linked_tables' => [
				'author' => [
					'type' => 'one_to_one',
					'name' => 'authors'
				],
				'comments' => [
					'type' => 'one_to_many',
					'name' => 'comments',
					'remote_column' => 'article_id',
					'local_property' => 'id'
				],
			],
		],
		'authors' => [
			'table_name' => 'auto_table_authors',
			'entity' => Application\Model\Author::class,
			'hydrator' => Application\Model\Hydrator\ObjectPropertyHydrator::class,
			'linked_tables' => [
				'cars' => [
					'type' => 'many_to_many',
					'remote_table' => 'cars',
					'mapping_table' => 'auto_table_car_author_map',
					'local_column' => 'id',
					'local_property' => 'id',
					'local_mapping_column' => 'author_id',
					'remote_mapping_column' => 'car_id',
					'remote_column' => 'id',
				]
			]
		],
		'auto_table_car_author_map' => [
			'table_name' => 'auto_table_car_author_map',
		],
		'cars' => [
			'table_name' => 'auto_table_cars',
			'entity' => Application\Model\Car::class,
			'hydrator' => Application\Model\Hydrator\ObjectPropertyHydrator::class,
			'hydrator' => Application\Model\Hydrator\ObjectPropertyHydrator::class,
			'linked_tables' => [
				'authors' => [
					'type' => 'many_to_many',
					'remote_table' => 'authors',
					'mapping_table' => 'auto_table_car_author_map',
					'local_column' => 'id',
					'local_property' => 'id',
					'local_mapping_column' => 'car_id',
					'remote_mapping_column' => 'author_id',
					'remote_column' => 'id',
				]
			]
		],
		'comments' => [
			'table_name' => 'auto_table_comments',
			'entity' => Application\Model\Comment::class,
			'hydrator' => Application\Model\Hydrator\ObjectPropertyHydrator::class,
			'linked_tables' => [
				'article' => [
					'alias_to' => 'articleId',
				],
				'articleId' => [
					'type' => 'one_to_one',
					'name' => 'articles',
				],
			],
		],
	],
	'service_manager' => [
		'factories' => [
			AutoTable\BaseTable::class => Zend\ServiceManager\Factory\InvokableFactory::class,
			AutoTable\AutoTableManager::class => AutoTable\AutoTableManagerServiceFactory::class,
			Application\Model\Custom::class => Zend\ServiceManager\Factory\InvokableFactory::class,
		],
		'shared' => [
			AutoTable\BaseTable::class => false,
			Application\Model\Custom::class => false,
		]
	]
];