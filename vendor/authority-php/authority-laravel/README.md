# Authority-Laravel

## A simple and flexible authorization system for Laravel 5

### Installation via Composer

Add Authority to your composer.json file to require Authority

    require : {
      "laravel/framework": "~5.0.16",
      "authority-php/authority-laravel": "dev-master"
    }

Now update Composer

  composer update

The last **required** step is to add the service provider to `config/app.php`

```php
    'Authority\AuthorityLaravel\AuthorityLaravelServiceProvider',
```

Congratulations, you have successfully installed Authority.  However, we have also included some other configuration options for your convenience.

### Additional (optional) Configuration Options

##### Add the alias (facade) to your Laravel app config file.

```php
    'Authority' => 'Authority\AuthorityLaravel\Facades\Authority',
```

This will allow you to access the Authority class through the static interface you are used to with Laravel components.

```php
  Authority::can('update', 'SomeModel');
```

##### Publish the Authority default configuration file

```php
  php artisan vendor:publish
```

This will place a copy of the configuration file at `config/authority.php`.  The config file includes an 'initialize' function, which is a great place to setup your rules and aliases.

```php
  // config/authority.php
    <?php

  return array(

    'initialize' => function($authority) {
      $user = $authority->getCurrentUser();

      // action aliases
      $authority->addAlias('manage', array('create', 'read', 'update', 'delete'));
          $authority->addAlias('moderate', array('read', 'update', 'delete'));

          // an example using the `hasRole` function, see below examples for more details
          if ($user->hasRole('admin')){
            $authority->allow('manage', 'all');
      }
    }

  );
```

##### Create Roles and Permissions Tables

We have provided a basic table structure to get you started in creating your roles and permissions.

Publish them to your migrations directory or copy them directly.

```php
  php artisan vendor:publish
```

Run the migrations

```php
  php artisan migrate
```

This will create the following tables

- roles
- role_user
- permissions

To utilize these tables, you can add the following methods to your `User` model.  You will also need to create Role and Permission Model stubs.

```php
  // app/User.php

  public function roles() {
        return $this->belongsToMany('App\Authority\Role');
    }

    public function permissions() {
        return $this->hasMany('App\Authority\Permission');
    }

  public function hasRole($key) {
    foreach ($this->roles as $role) {
      if ($role->name === $key) {
        return true;
      }
    }

    return false;
  }

  // app/Authority/Role.php
    <?php

    use Illuminate\Database\Eloquent\Model;

  class Role extends Model {}

  // app/Authority/Permission.php
    <?php

    use Illuminate\Database\Eloquent\Model;

  class Permission extends Model {}
```

Lastly, in your Authority config file which you copied over in the previous configuration step.  You can add some rules:

```php
  // config/authority.php
    <?php

  return array(

    'initialize' => function($authority) {

      $user = $authority->getCurrentUser();

      // action aliases
      $authority->addAlias('manage', array('create', 'read', 'update', 'delete'));
          $authority->addAlias('moderate', array('read', 'update', 'delete'));

          // an example using the `hasRole` function, see below examples for more details
          if ($user->hasRole('admin')) {
            $authority->allow('manage', 'all');
      }

      // loop through each of the users permissions, and create rules
      foreach ($user->permissions as $perm) {
        if ($perm->type == 'allow') {
          $authority->allow($perm->action, $perm->resource);
        } else {
          $authority->deny($perm->action, $perm->resource);
        }
      }
    }

  );
```

## General Usage

```php
  // If you added the alias to `config/app.php` then you can access Authority, from any Controller, View, or anywhere else in your Laravel app like so:
  if (Authority::can('create', 'User')) {
    User::create(array(
      'username' => 'someuser@test.com'
    ));
  }

  // If you just chose to use the service provider, you can use the IoC container to resolve your instance
  $authority = App::make('authority');
```

## Interface

There are 5 basic functions that you need to be aware of to utilize Authority.

- **allow**: *create* a rule that will *allow* access a resource

  example 1:
  ```php
      Authority::allow('read', 'User');
  ```

  example 2, using an extra condition:
  ```php
      Authority::allow('manage', 'User', function($self, $user){
        return $self->getCurrentUser()->id === $user->id;
      });
  ```

- **deny**: *create* a rule that will *deny* access a resource

  example 1:
  ```php
      Authority::deny('create', 'User');
  ```

  example 2, using an extra condition:
  ```php
      Authority::deny('delete', 'User', function ($self, $user) {
            return $self->getCurrentUser()->id === $user->id;
        });
    ```

- **can**: check if a user *can* access a resource

  example:
  ```php
      Authority::can('read', 'User', $user);
  ```

- **cannot**: check if a use *cannot* access a resource

  example:
  ```php
      Authority::cannot('create', 'User');
  ```

- **addAlias**: alias together a group of actions

  this example aliases together the CRUD methods under a name of `manage`
  ```php
      Authority::alias('manage', array('create', 'read', 'update', 'delete'));
  ```

## Converting to this library, where you previously had been using the IoC container to resolve an instance.

The service provider will merely create a new instance of Authority, and pass in the currently logged in user to the constructor.  This should be basically the same process that you were doing in your IoC registry.  This means that any code you have used in the past should still work just fine!  However it is recommended that you move your rule definitions into the provided configuration file.
