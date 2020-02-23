# ThemePlate Cache

## Usage

```php
use ThemePlate\Cache;

Cache::remember( 'unique_key', function() {
	return expensive_task();
}, MINUTE_IN_SECONDS );

Cache::forget( 'unique_key' );

Cache::file( 'special_key', 'path_to_file' );
```

### Cache::remember( $key, $callback, $expiration )

Retrieve content from the cache or, if it doesn't exist, execute $callback and its result is returned then saved

- **$key** *(string)(Required)* Unique cache key to use
- **$callback** *(callable)(Required)* Function that returns data to store
- **$expiration** *(int)(Optional)* Number of seconds before entry expires. Default 0 (forever)

### Cache::forget( $key, $default )

Retrieve and delete the cache

- **$key** *(string)(Required)* Unique cache key to use
- **$default** *(mixed)(Optional)* To return if cache doesn't exist. Default `null`

### Cache::file( $key, $path )

Like `remember` but, uses the file contents and no expiration, automatically updates if the file is modified instead

- **$key** *(string)(Required)* Unique cache key to use
- **$path** *(string)(Required)* Path of the file to read
