# Laravel Modules With Livewire

Using [Laravel Livewire](https://github.com/livewire/livewire) in [Laravel Modules](https://github.com/nWidart/laravel-modules) package with automatically registered livewire components for every modules.

<p align="center">
    <img src="https://dev.mhmiton.com/laravel-modules-livewire-example/public/assets/images/laravel-modules-livewire.png" alt="laravel-modules-livewire">
</p>

<p align="left">
    <strong>Example Source Code: </strong><a href="https://github.com/mhmiton/laravel-modules-livewire-example" target="_blank">https://github.com/mhmiton/laravel-modules-livewire-example</a>
</p>

<p align="left">
    <strong>Example Live Demo: </strong> <a href="https://dev.mhmiton.com/laravel-modules-livewire-example" target="_blank">https://dev.mhmiton.com/laravel-modules-livewire-example</a>
</p>

### Installation:

Install through composer:

```
composer require mhmiton/laravel-modules-livewire
```

Publish the package's configuration file:

```
php artisan vendor:publish --tag=modules-livewire-config
```

### Making Components:

**Command Signature:**

`php artisan module:make-livewire <Component> <Module> --view= --force --inline --stub= --custom`

**Example:**

```
php artisan module:make-livewire Pages/AboutPage Core
```

```
php artisan module:make-livewire Pages\\AboutPage Core
```

```
php artisan module:make-livewire pages.about-page Core
```

**Force create component if the class already exists:**

```
php artisan module:make-livewire Pages/AboutPage Core --force
```

**Output:**

```
COMPONENT CREATED  🤙

CLASS: Modules/Core/Livewire/Pages/AboutPage.php
VIEW:  Modules/Core/Resources/views/livewire/pages/about-page.blade.php
TAG: <livewire:core::pages.about-page />
```

**Inline Component:**

```
php artisan module:make-livewire Core Pages/AboutPage --inline
```

**Output:**

```
COMPONENT CREATED  🤙

CLASS: Modules/Core/Livewire/Pages/AboutPage.php
TAG: <livewire:core::pages.about-page />
```

**Modifying Stubs:**

Publish the package's stubs:

```
php artisan vendor:publish --tag=modules-livewire-stub
```

After publishing the stubs, will create these files. And when running the make command, will use these stub files by default.

```
stubs/modules-livewire/livewire.inline.stub
stubs/modules-livewire/livewire.stub
stubs/modules-livewire/livewire.view.stub
```

**You're able to set a custom stub directory for component with (--stub) option.**

```
php artisan module:make-livewire Core Pages/AboutPage --stub=about
```

```
php artisan module:make-livewire Core Pages/AboutPage --stub=modules-livewire/core
```

```
php artisan module:make-livewire Core Pages/AboutPage --stub=./
```

**Extra Option (--view):**

**You're able to set a custom view path for component with (--view) option.**

**Example:**

```
php artisan module:make-livewire Pages/AboutPage Core --view=pages/about
```

```
php artisan module:make-livewire Pages/AboutPage Core --view=pages.about
```

**Output:**

```
COMPONENT CREATED  🤙

CLASS: Modules/Core/Livewire/Pages/AboutPage.php
VIEW:  Modules/Core/Resources/views/livewire/pages/about.blade.php
TAG: <livewire:core::pages.about-page />
```
### Rendering Components:

`<livewire:{module-lower-name}::component-class-kebab-case />`

**Example:**

```
<livewire:core::pages.about-page />
```
### Custom Module:

**To create components for the custom module, should be add custom modules in the config file.**

The config file is located at `config/modules-livewire.php` after publishing the config file.

Remove comment for these lines & add your custom modules.

```
    /*
    |--------------------------------------------------------------------------
    | Custom modules setup
    |--------------------------------------------------------------------------
    |
    */

    // 'custom_modules' => [
    //     'Chat' => [
    //         'path' => base_path('libraries/Chat'),
    //         'module_namespace' => 'Libraries\\Chat',
    //         // 'namespace' => 'Livewire',
    //         // 'view' => 'Resources/views/livewire',
    //         // 'name_lower' => 'chat',
    //     ],
    // ],
```

**Custom module config details**

> **path:** Add module full path (required).
>
> **module_namespace:** Add module namespace (required).
>
> **namespace:** By default using `config('modules-livewire.namespace')` value. You can set a different value for the specific module.
>
> **view:** By default using `config('modules-livewire.view')` value. You can set a different value for the specific module.
>
> **name_lower:** By default using module name to lowercase. If you set a custom name, module components will be register by custom name.
>

### License

Copyright (c) 2021 Mehediul Hassan Miton <mhmiton.dev@gmail.com>

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
