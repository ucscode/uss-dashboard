## User Synthetics Dashboard Module

The uss dashboard created by [the author of user synthetics](https://github.com/ucscode) is a light-weight module that enable developers create powerful user management system.

### How to create custom authentication pages:

```php
udash::config('auth-page', function() {
	$page = uss::query(1);
	if( $page == 'signup' ) ; // do signup
	else if( $page == 'reset' ) ;// do reset
	else if( empty($page) ) ;// do login
	else ; // do whatever
});
```