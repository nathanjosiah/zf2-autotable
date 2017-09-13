## Retrieving Data

### Flat table

Using the following table `articles`:

id | title
-- | --
1 | Sweet Baby Ray's is the best!
2 | PB&J is yummy
3 | I like Jellyfish

First model the table:

```php
namespace Application\Model;
class Article {
  public $id,$title;
}
```

Then in the config:

```php
'auto_tables' => [
  'articles' => [
    'table_name' => 'articles',
    'entity' => \Application\Model\Article::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
  ]
]
```

And finally, simple use it:

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->get('articles');
$entity = $table->fetchWithId(2);

// Outputs: "PB&J is yummy"
echo $entity->title;
```


### One-to-many relationships


With a table `articles`:

id | title | author
-- | -- | ---
1 | Sweet Baby Ray's is the best! | 1
2 | PB&J is yummy | 1
3 | I like Jellyfish | 2

and another table `authors`:

id | Name
-- | --
1 | Nathan Smith
2 | Fake Name

Start by modeling the entity for both:

The article:

```php
namespace Application\Model;
class Article {
  public $id,$title,$author;
}
```

and the author

```php
namespace Application\Model;
class Author {
  public $id,$name;
}
```

Then in the config, define both the `articles` and `authors` table and use the `linked_tables` key to configure the relationship:

```php
'auto_tables' => [
  'articles' => [
    'table_name' => 'articles',
    'entity' => \Application\Model\Article::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
    'linked_tables' => [
      'author' => [
        'name' => 'authors',
        'type' => 'one_to_one',
      ]
    ]
  ],
  'authors' => [
    'table_name' => 'authors',
    'entity' => \Application\Model\Author::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
  ]
]
```

Then use it:
```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->get('articles');
$article = $table->fetchWithId(3);

// Outputs: "I like Jellyfish"
echo $article->title;

// Outputs: "Fake Name"
echo $article->author->name;
```

### Many-to-many relationships


With a table `articles`:

id | title
-- | --
1 | Sweet Baby Ray's is the best!
2 | PB&J is yummy
3 | I like Jellyfish

and another table `authors`:

id | Name
-- | --
1 | Nathan Smith
2 | Fake Name
3 | Jimmy Boi
4 | John Doe

and a mapping table `articles_authors_map`:

article_id | author_id
-- | --
1 | 1
1 | 3
2 | 3
3 | 4
3 | 2

Start by modeling the entity for both:

The article:

```php
namespace Application\Model;
class Article {
  public $id,$title,$authors;
}
```

and the author

```php
namespace Application\Model;
class Author {
  public $id,$name,$articles;
}
```

Then in the config, define both the `articles` , `authors`, and `articles_authors_map` tables and use the `linked_tables` key for the `articles` and `authors` table to configure the two-way relationship:

```php
'auto_tables' => [
  'articles' => [
    'table_name' => 'articles',
    'entity' => \Application\Model\Article::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
    'linked_tables' => [
      'authors' => [
        'type' => 'many_to_many',
        'remote_table' => 'authors',
        'mapping_table' => 'articles_authors_map',
        'local_column' => 'id',
        'local_property' => 'id',
        'local_mapping_column' => 'article_id',
        'remote_mapping_column' => 'author_id',
        'remote_column' => 'id',
      ]
    ]
  ],
  'authors' => [
    'table_name' => 'authors',
    'entity' => \Application\Model\Author::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
    'linked_tables' => [
      'articles' => [
        'type' => 'many_to_many',
        'remote_table' => 'authors',
        'mapping_table' => 'articles_authors_map',
        'local_column' => 'id',
        'local_property' => 'id',
        'local_mapping_column' => 'author_id',
        'remote_mapping_column' => 'article_id',
        'remote_column' => 'id',
      ]
    ]
  ],
  'articles_authors_map' => [
    'table_name' => 'articles_authors_map',
  ]
]
```

Then use it:
```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->get('articles');
$article = $table->fetchWithId(3);

// Outputs: "I like Jellyfish"
echo $article->title;

// Outputs: "John Doe" and "Fake Name"
foreach($article->authors as $author) {
  echo $author->name;
}

$table = $manager->get('authors');
$author = $table->fetchWithId(3);

// Outputs: "Jimmy Boi"
echo $author->name;

// Outputs: "Sweet Baby Ray's is the best!" and "PB&J is yummy"
foreach($author->articles as $article) {
  echo $article->title;
}
```

### Relationships aren't one level deep

Any linked tabled contain all the defined relationships all the time

With a table `articles`:

id | title | author
-- | -- | ---
1 | Sweet Baby Ray's is the best! | 1
2 | PB&J is yummy | 1
3 | I like Jellyfish | 2

and another table `authors`:

id | Name | Car
-- | -- | --
1 | Nathan Smith | 2
2 | Fake Name | 1

and another table `cars`:

id | Name
-- | --
1 | Honda Civic
2 | Tesla Model 3

Start by modeling the entity for all:

The article:

```php
namespace Application\Model;
class Article {
  public $id,$title,$author;
}
```

and the author

```php
namespace Application\Model;
class Author {
  public $id,$name,$car;
}
```

and the car

```php
namespace Application\Model;
class Car {
  public $id,$name,$car;
}
```

Then in the config, define the `articles`,  `authors`, and `cars` tables and use the `linked_tables` key to configure the relationships:

```php
'auto_tables' => [
  'articles' => [
    'table_name' => 'articles',
    'entity' => \Application\Model\Article::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
    'linked_tables' => [
      'author' => [
        'name' => 'authors',
        'type' => 'one_to_one',
      ]
    ]
  ],
  'authors' => [
    'table_name' => 'authors',
    'entity' => \Application\Model\Author::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
    'linked_tables' => [
      'car' => [
        'name' => 'cars',
        'type' => 'one_to_one',
      ]
    ]
  ],
  'cars' => [
    'table_name' => 'cars',
    'entity' => \Application\Model\Car::class,
    'hydrator' => \Zend\Hydrator\ObjectProperty::class,
  ]
]
```

Then use it:
```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->get('articles');
$article = $table->fetchWithId(3);

// Outputs: "I like Jellyfish"
echo $article->title;

// Outputs: "Fake Name"
echo $article->author->name;

// Outputs: "Honda Civic"
echo $article->author->car->name;
```