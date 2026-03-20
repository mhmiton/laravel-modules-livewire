# Laravel Modules + Livewire

Integrating [Laravel Livewire](https://github.com/livewire/livewire) with [Laravel Modules](https://github.com/nWidart/laravel-modules) enables seamless Livewire functionality across all your application modules.

<p align="center">
    <img src="https://dev.mhmiton.com/laravel-modules-livewire-example/public/assets/images/laravel-modules-livewire.png" alt="laravel-modules-livewire">
</p>

### Examples:
<strong>Live Demo: </strong> <a href="https://dev.mhmiton.com/laravel-modules-livewire-example" target="_blank">https://dev.mhmiton.com/laravel-modules-livewire-example</a> - [Source Code](https://github.com/mhmiton/laravel-modules-livewire-example)

## Installation:
```
composer require mhmiton/laravel-modules-livewire
```

### Configurations
Publish the package's configuration file using the following command:

```
php artisan vendor:publish --tag=modules-livewire:config
```

## Components

#### Command Signature
`php artisan module:make-livewire {component} {module} {--sfc|mfc|class} {--options}`

#### Options

##### `--force` *(Force create component if the component already exists)*
```
php artisan module:make-livewire index User --force
```

##### `--emoji` *(Use emoji (⚡) in file/directory names (true or false)*
```
php artisan module:make-livewire index User --emoji=false
```

##### `--test` *(Create component with test file)*
```
php artisan module:make-livewire index User --test
```

##### `--js` *(Create component with js file)*
```
php artisan module:make-livewire index User --js
```

##### `--view` *(Set a custom view path for component)*
```
php artisan module:make-livewire Index User --class --view=pages/index
```
> Note: Only registered view namespaces will be supported. By default, registered view namespaces are 'livewire' and 'pages' in the config.

##### `--stubs` *(You can set a custom stub directory for a component)*
```
php artisan module:make-livewire Index User --stub=modules-livewire/user
```

### Rendering Components
`<livewire:{module-lower-name}::component-class-kebab-case />`

#### Example
```
<livewire:user::profile />
```

## Single File Components (SFC)
```
php artisan module:make-livewire index User --sfc
```

#### Output
```
COMPONENT CREATED - SFC  🤙

VIEW:  modules/User/resources/views/livewire/⚡index.blade.php
TAG: <livewire:user::index />
```

## Multi-File Components (MFC)
```
php artisan module:make-livewire index User --mfc
```

#### Output
```
COMPONENT CREATED - MFC  🤙

CLASS:  modules/User/resources/views/livewire/⚡index/index.php
VIEW:  modules/User/resources/views/livewire/⚡index/index.blade.php
TAG: <livewire:user::index />
```

## Class-based Components
```
php artisan module:make-livewire Index User --class
```

#### Output
```
COMPONENT CREATED - CLASS BASED  🤙

CLASS: Modules/User/app/Livewire/Index.php
VIEW:  Modules/User/resources/views/livewire/index.blade.php
TAG: <livewire:user::index />
```

## Inline (Class-based) Component
```
php artisan module:make-livewire Index User --class --inline
```

#### Output
```
COMPONENT CREATED - CLASS BASED  🤙

CLASS: Modules/User/app/Livewire/Index.php
TAG: <livewire:user::index />
```

## Stubs

### Publishing the package's stubs

```
php artisan vendor:publish --tag=modules-livewire:stub
```

After publishing the stubs, these files will be created. And when running the make command, it will use these stub files by default.

#### Single File Component (SFC)
- stubs/modules-livewire/livewire-sfc.stub

#### Multi-File Component (MFC)
- stubs/modules-livewire/livewire-mfc-class.stub
- stubs/modules-livewire/livewire-mfc-view.stub
- stubs/modules-livewire/livewire-mfc-test.stub
- stubs/modules-livewire/livewire-mfc-js.stub

#### Class-based Component
- stubs/modules-livewire/livewire.inline.stub
- stubs/modules-livewire/livewire.stub
- stubs/modules-livewire/livewire.view.stub

#### Volt
- stubs/modules-livewire/volt-component-class.stub
- stubs/modules-livewire/volt-component.stub

## Form Components:

### Command Signature
`php artisan module:make-livewire-form {component} {module} {--options}`

### Example

```
php artisan module:make-livewire-form Forms/CreateUserForm User
```
or
```
php artisan module:make-livewire-form forms.create-user-form User
```

#### Output

```
COMPONENT CREATED  🤙

CLASS: Modules/User/app/Livewire/Forms/CreateUserForm.php
```

## Volt Components

### Command Signature
`php artisan module:make-volt {component} {module} {--options}`

### Example
```
php artisan module:make-volt counter User
```

#### Output
```
VOLT COMPONENT CREATED  🤙

VIEW:  modules/User/resources/views/livewire/counter.blade.php
TAG: <livewire:user::counter />
```

### Route
```
use Livewire\Volt\Volt;

Volt::route('/user/counter', 'user::counter');
```

## Custom Module

To create components for the custom module, add custom modules in the `config/modules-livewire.php` config file.

Remove comments to enable the custom modules.

```
/*
|--------------------------------------------------------------------------
| Custom modules setup
|--------------------------------------------------------------------------
|
*/

'custom_modules' => [
    // 'Chat' => [
    //     'name_lower' => 'chat',
    //     'path' => base_path('libraries/Chat'),
    //     'app_path' => 'src',
    //     'module_namespace' => 'Libraries\\Chat',
    //     'namespace' => 'Livewire',
    //     'view' => 'resources/views/livewire',
    //     'views_path' => 'resources/views',
    //     'volt_view_namespaces' => ['livewire', 'pages'],
    // ],
],
```

### Configurations
- name_lower: Module name in lower case (required).
- path: Module full path (required).
- module_namespace: Module namespace (required).
- namespace: By default, using `config('modules-livewire.namespace')` value. You can set a different value for the specific module.
- view: By default, using `config('modules-livewire.view')` value. You can set a different value for the specific module.
- views_path: Module resource view path (required).
- volt_view_namespaces: By default, using `config('modules-livewire.volt_view_namespaces')` value. You can set a different value for the specific module.

## License
Copyright (c) 2021 Mehediul Hassan Miton <mhmiton.dev@gmail.com>

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
