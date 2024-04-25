# ThemePlate Cache

## Usage

```php
use ThemePlate\Cache;

Cache::remember( 'unique_key', function() {
    return expensive_task();
}, MINUTE_IN_SECONDS );

Cache::forget( 'unique_key' );

Cache::file( 'special_key', 'path_to_file' );

$processor = Cache::processor();

$processor->report( function( $output ) {
    error_log( print_r( $output, true ) );
} );


function hourly_moment() {
    return 'to remember ' . time();
}

Cache::remember( 'unique_key', 'hourly_moment', HOUR_IN_SECONDS );
```

### Force refresh value/s

`<WP_HOME>/?tcs_refresh=<single_key>`

`<WP_HOME>/?tcs_refresh[]=<key1>&tcs_refresh[]=<key2>`

> _works only when logged-in_

### Cache::remember( $key, $callback, $expiration )

Retrieve content from the cache or, if it doesn't exist, execute $callback and its result is returned then saved

- **$key** _(string)(Required)_ Unique cache key to use
- **$callback** _(callable)(Required)_ Function that returns data to store
- **$expiration** _(int)(Optional)_ Number of seconds before entry expires. Default 0 (forever)

### Cache::forget( $key, $default )

Retrieve and delete the cache

- **$key** _(string)(Required)_ Unique cache key to use
- **$default** _(mixed)(Optional)_ To return if cache doesn't exist. Default `null`

### Cache::file( $key, $path )

Like `remember` but, uses the file contents and no expiration, automatically updates if the file is modified instead

- **$key** _(string)(Required)_ Unique cache key to use
- **$path** _(string)(Required)_ Path of the file to read

### Cache::processor()

Support for soft-expiration, `Cache::remember`\* and `Cache::file` updates in the background

> \*Except for using anonymous function as callback (closure)
