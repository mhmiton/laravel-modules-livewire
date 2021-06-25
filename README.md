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

Install through Composer:

```
composer require mhmiton/laravel-modules-livewire
```

Publish the package's configuration file:

```
php artisan vendor:publish --provider="Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider"
```

### Making Components:

**Command Signature:**

`php artisan module:make-livewire <Component> <Module> --view= --force --inline`

**Example:**

```
php artisan module:make-livewire Pages/AboutPage Core

php artisan module:make-livewire Pages\\AboutPage Core

php artisan module:make-livewire pages.about-page Core
```

**Force create component if the class already exists:**

`php artisan module:make-livewire Pages/AboutPage Core --force`

**Component Files:**

```
Class: Modules/Core/Http/Livewire/Pages/AboutPage.php
View: Modules/Core/Resources/views/livewire/pages/about-page.blade.php
```

**Inline Component:**

`php artisan module:make-livewire Core Pages/AboutPage --inline`

**Component File:**

`Class: Modules/Core/Http/Livewire/Pages/AboutPage.php`


**Extra Option (--view):**

**You're able to set a custom view path for Component with (--view) option.**

**Example -**

```
php artisan module:make-livewire Pages/AboutPage Core --view=pages/about

or

php artisan module:make-livewire Pages/AboutPage Core --view=pages.about
```

**Component Files:**

```
Class: Modules/Core/Http/Livewire/Pages/AboutPage.php
View: Modules/Core/Resources/views/livewire/pages/about.blade.php
```


### Rendering Components:

`<livewire:{module-lower-name}::component-class-kebab-case />`

**Example -**

`<livewire:core::pages.about-page />`

### License

Copyright (c) 2021 Mehediul Hassan Miton <mhmiton.dev@gmail.com>

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
