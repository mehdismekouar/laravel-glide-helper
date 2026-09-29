# Changelog

All notable changes to `laravel-glide-helper` will be documented in this file.

## v2.0.0 - 2026-09-29

Laravel 13 support, including new apps that use Guzzle 8.

### Changed

- Uses league/glide 3 directly instead of spatie/laravel-glide, which could not be installed next to Guzzle 8.
- The image driver is now set in `config/glide-helper.php` (`'driver' => 'gd'` or `'imagick'`).
- Supports Laravel 10 to 13 on PHP 8.1 and later.

### Removed

- A facade alias pointing to a class that did not exist.
- `configure.php`, left over from the package skeleton.

### Upgrading from 1.x

Your `glide()` calls and their URLs do not change. If you used Imagick, move `driver` from `config/laravel-glide.php` to `config/glide-helper.php`. If your own code uses `Spatie\Glide\GlideImage`, require `spatie/laravel-glide` yourself.

## v1.0.0 - 2025-10-20

First release: `glide()` helper for on-the-fly image manipulation in Blade.
