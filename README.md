# Laravel Glide Helper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehdismekouar/laravel-glide-helper.svg?style=flat-square)](https://packagist.org/packages/mehdismekouar/laravel-glide-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/mehdismekouar/laravel-glide-helper.svg?style=flat-square)](https://packagist.org/packages/mehdismekouar/laravel-glide-helper)

A simple Laravel helper function for on-the-fly image manipulation using Spatie Glide.

## Features

- 🚀 **On-the-fly image manipulation** - Resize, crop, and transform images dynamically
- 💾 **Automatic caching** - Generated images are cached to avoid regeneration
- 🔧 **Configurable defaults** - Set global parameters for consistent image processing
- 📁 **Multiple source support** - Works with storage/ and public/ directories
- 🌐 **External URL handling** - Passes through external URLs unchanged
- ⚡ **Performance optimized** - Only processes when necessary

## Installation

Install the package via Composer:

```bash
composer require mehdismekouar/laravel-glide-helper
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="laravel-glide-helper-config"
```

This is the contents of the published config file:

```php
return [
    'defaults' => [
        'q' => 90,          // Quality (1-100)
        'fm' => 'webp',     // Format (webp, jpg, png, gif)
        'fit' => 'max'      // Fit (contain, max, fill, stretch, crop)
    ],
    'output_dir' => 'manipulated', // Directory for cached images
];
```

## Usage

### Basic Usage

```php
// In Blade templates
<img src="{{ glide('/storage/photos/image.jpg', ['w' => 300, 'h' => 200]) }}" alt="Resized">
```

### Common Parameters

```php
// Resize
glide($image, ['w' => 300, 'h' => 200])

// Crop to exact dimensions
glide($image, ['w' => 200, 'h' => 200, 'fit' => 'crop'])

// Change format and quality
glide($image, ['fm' => 'webp', 'q' => 85])

// Apply filters
glide($image, ['filt' => 'greyscale', 'blur' => 5])
```

### Supported Parameters

All [Glide parameters](https://glide.thephpleague.com/2.0/api/quick-reference/) are supported:

- **Size**: `w` (width), `h` (height), `fit` (contain, max, fill, stretch, crop)
- **Format**: `fm` (webp, jpg, png, gif), `q` (quality 1-100)
- **Effects**: `blur`, `sharp`, `filt` (greyscale)

## How It Works

1. **Path Resolution**: Automatically detects if image is in `storage/` or `public/`
2. **Caching**: Creates a unique hash based on source file and parameters
3. **Generation**: Uses Spatie Glide to manipulate image only if not cached
4. **URL Generation**: Returns asset URL for the manipulated image

External URLs are passed through unchanged.

## Requirements

- PHP 8.1+
- Laravel 10+
- `spatie/laravel-glide` package (automatically installed)

## Credits

- [Mehdi Mekouar](https://github.com/mehdismekouar)
- Built on top of [Spatie Laravel Glide](https://github.com/spatie/laravel-glide)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
