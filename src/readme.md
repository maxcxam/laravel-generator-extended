# Laravel 9 Extended Migration Generator


L5 includes a bunch of generators out of the box, so this package only needs to add a few things, like:

- `make:model`
- `make:model {ModelName}`



## Usage on Laravel 9

### Step 1: Install Through Composer

```
composer require maxcxam/laravel-generator-extended
```

### Step 2: Add the Service Provider

You'll only want to use these generators for local development, so you don't want to update the production  `providers` array in `config/app.php`. Instead, add the provider in `app/Providers/AppServiceProvider.php`, like so:

```php
public function register()
{
    if ($this->app->environment() == 'development') {
        $this->app->register('Maxcxam\Generators\GeneratorsServiceProvider');
    }
}
```


### Step 3: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands in the `make:*` namespace section.

## Examples

- [Migrations With Schema](#migrations-with-schema)


### Migrations With Schema

```
php artisan make:model Product
```
answer for a some questions like field names, types, nullable, <b>relations</b> etc

...this will give you:

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table) {
			$table->increments('id');
			$table->string('username');
			$table->string('email')->nullable(FALSE);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
```

```
Available Relations is 'ManyToOne', 'ManyToMany'
```

```
Available field types is 'string', 'text', 'array'
```