# Saving Records

Once your relationships are configured to retrieve records, you can save the records as well as seen in the section below.

## Flat record

With a table `articles`:

id | title
-- | --
1 | Sweet Baby Ray's is the best!
2 | PB&J is yummy
3 | I like Jellyfish


** Create a record **

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$article = $manager->createNew('articles');
$article->title "I like AutoTable"

// Persist all pending changes
$manager->flush();

// See the results
$article = $articles->fetchWithId(4);

// Outputs: "I like AutoTable"
echo $artcle->title;
```

** Make changes **

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$articles = $manager->getTable('articles');
// Get the article
$article = $articles->fetchWithId(3);

// Outputs: "I like Jellyfish"
echo $article->title

// Make changes
$article->title = "Jellyfish are gross"

// Persist all pending changes
$manager->flush();

// See the results
$article = $articles->fetchWithId(3);

// Outputs: "Jellyfish are gross"
echo $artcle->title;
```

** Delete a Record **

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$article = $articles->fetchWithId(3);

// Delete the article
$manager->delete($article);

// Persist all pending changes
$manager->flush();

// $article will be null
$article = $articles->fetchWithId(3);
```

## One-to-Many

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$articles = $manager->getTable('articles');

// Create the comment
$comment1 = $manager->createNew('comments');
$comment1->comment = 'This is a new comment';

// Bind it to the article
$comment1->article = $articles->fetchWithId(1);
// or
$comment1->article = 1;

$comment2 = $manager->createNew('comments');
$comment2->comment = 'Comments are great';

// Bind it to the article
$comment2->article = $articles->fetchWithId(1);
// or
$comment2->article = 1;

// Persist all pending changes
$manager->flush();

// See the results
$article = $articles->fetchWithId(1);
// Outputs "This is a new comment" and "Comments are great"
foreach($article->comments as $comment) {
	echo $comment->comment;
}
```

## Many-to-Many

With a table `authors`:

id | name
-- | --
1 | Nathan Smith
2 | John Doe

and another table `cars`:

id | name
-- | --
1 | Tesla Model 3
2 | Honda Civic
3 | Ice Cream Bus
4 | Tow Truck

and a mapping table `author_car_map`:

author_id | car_id
-- | --
1 | 3
2 | 4


** Link a Record **

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$authors = $manager->getTable('authors');
$cars = $manager->getTable('cars');

// Get the author and desired car
$author = $authors->fetchWithId(1);
$car = $cars->fetchWithId(1);

// Give a Tesla to Nathan.
$manager->link($car,$author);

$manager->flush();

foreach($author->cars as $car) {
	// Outputs: "Ice Cream Bus" and "Tesla Model 3"
	echo $car->name;
}
```

** Unlink a Record **

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$authors = $manager->getTable('authors');
$cars = $manager->getTable('cars');

// Get the author and desired car
$author = $authors->fetchWithId(1);
$car = $cars->fetchWithId(3);

// Take an Ice Cream bus away from Nathan :"( sad day.
$manager->unlink($car,$author);

$manager->flush();

foreach($author->cars as $car) {
	// Assuming you linked the record in the previous example above, Outputs: "Tesla Model 3"
	echo $car->name;
}
```