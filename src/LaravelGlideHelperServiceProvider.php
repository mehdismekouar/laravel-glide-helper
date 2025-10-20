<?php

namespace Mehdismekouar\LaravelGlideHelper;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelGlideHelperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-glide-helper')
            ->hasConfigFile('glide-helper');
    }
}
