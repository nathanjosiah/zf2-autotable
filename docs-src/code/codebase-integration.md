# Integrating into your code

For most cases it should be fairly straighforward to use. However, there are some things to keep in mind.

## All tracked objects are `Proxy` objects

All of the objects within the system are basically wrapped around your native objects. Changes made to objects that aren't being tracked won't be persisted when `flush` is called.

Here are a few examples:

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->getTable('articles');
$entity = $table->fetchWithId(2);

// Outputs: "AutoTable\Proxy"
echo get_class($entity);
```


```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

$untracked_article = new \Application\Model\Article();

// Outputs: "Application\Model\Article"
echo get_class($untracked_article);

$tracked_article = $manager->track($untracked_article,'articles');

// Outputs: "AutoTable\Proxy"
echo get_class($tracked_article);
```


## Tracking changes to untracked objects
As previously stated, any changes made to objects untracked won't be persisted when `flush` is called. However, you can still work with your raw objects as long as you track them before calling `flush`.

> _**Note:** Keep in mind, the features of this module aren't available for raw objects. None of your linked tables will be available unless you are operating on a `Proxy` object._

### Saving a new record using an untracked object

```php
$untracked_article = new \Application\Model\Article();
$untracked_article->title = 'Yay, an article!';

// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

// Here is where we track it
$tracked_article = $manager->track($untracked_article,'articles');

// Force a sync when flush is called
$manager->queueSync($tracked_article);

// Changes will be picked up and saved. In this case, a new record.
$manager->flush();
```


### Updating a record using an untracked object

```php
// Either created like this or retrieved out of a Proxy object with __getObject()
$untracked_article = new \Application\Model\Article();
$untracked_article->id = 123;
$untracked_article->title = 'Yay, an article!';

// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);

// Here is where we track it
$tracked_article = $manager->track($untracked_article,'articles');

// Force a sync when flush is called
$manager->queueSync($tracked_article);

// Changes will be picked up and saved.
$manager->flush();
```

The call to `queueSync` is unecessary if you make changes after it is tracked:

```php
$untracked_article = new \Application\Model\Article();

// Track it
$tracked_article = $manager->track($untracked_article,'articles');

// Any changes automatically trigger a sync
$untracked_article->title = 'Yay, an article!';

$manager->flush();
```

### Extracting the underlying object from the Proxy using `Proxy::__getObject`

You are also able to extract the underlying object as seen below.

> _**Note:** Doing this removes it from tracking.
  To persist changes to the extracted untracked object, use one of the methods from above._

```php
// $serviceLocator is an instance of the main ServiceManager
$manager = $serviceLocator->get(\AutoTable\AutoTableManager::class);
$table = $manager->getTable('articles');

$tracked_article = $table->fetchWithId(2);

// Outputs: "AutoTable\Proxy"
echo get_class($tracked_article);

$untracked_article = $tracked_article->__getObject();

// Outputs: "Application\Model\Article"
echo get_class($untracked_article);
```

## Using a custom table class

By default the `AutoTable\BaseTable` is used for all of the lookups. If you wish to implement your own for custom data retrievals, simply set it up in the ServiceManager and set the `table` key in the [table config](/config/).

It must implement the `AutoTable\TableInterface` or simply extend `AutoTable\BaseTable`. You don't have to do anything special with the results so long as the data returned can be hydrated by the specified hydrator. Refer to the BaseTable for examples of code.

The only important note would be to set `shared` to false for your service manager definition of the extended table. Otherwise, the table would get cached incorrectly and things will break.