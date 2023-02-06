# FileCache
I had to do with a hosting company that just refused to enable memcache/memcached/redis because it wasn't a one-click install option in CPanel. So that's why I wrote this class as the storage drives are fast SSDs.

I'm including both a procedural version and a class version.

## Tested on:
PHP 7.3
PHP 7.4
PHP 8.0
PHP 8.1
PHP 8.2

## How is this different from memcache/memcached/redis
The main difference is that this stores the data in json files on the harddrive and when you fetch the data you specify how old the data is that you load rather than how old the data is until it expires when you save it as you do in a memory cache server as this doesn't contain a server software.


## Procedural usage
Include the cache.php for the Procedural version such as:
```php
<?php
require 'cache.php';
```

### fileCacheGet($key, $ttl)
$key: String value for your cache key\
$ttl: How old cache you can load specified as seconds, default 120 seconds

### fileCacheSet($key, $data)
$key: String value for your cache key\
$data: The data to store
  
### fileCacheDelete($key)
$key: Deletes the cache file for this cache

## Class usage
Install using composer
```sh
composer require palma/file-cache
```

Include the class_filecache.php for the Class version
```php
<?php
require './vendor/autoload.php';
$cache = new FileCache();
```

### $cache::get($key, $ttl)
$key: String value for your cache key\
$ttl: How old cache you can load specified as seconds, default 120 seconds

### $cache::set($key, $data)
$key: String value for your cache key\
$data: The data to store
  
### $cache::delete($key)
$key: Deletes the cache file for this cache

## Author
Peter Palma

## License
MIT License, see included LICENSE file.
