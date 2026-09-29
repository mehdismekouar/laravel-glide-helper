# Laravel Glide Helper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehdismekouar/laravel-glide-helper.svg?style=flat-square)](https://packagist.org/packages/mehdismekouar/laravel-glide-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/mehdismekouar/laravel-glide-helper.svg?style=flat-square)](https://packagist.org/packages/mehdismekouar/laravel-glide-helper)

A simple Laravel helper function for on-the-fly image manipulation using League Glide.

It adds one function to your Laravel application: `glide()`. Hand it an image and the size you want, and it returns the URL of a resized, compressed WebP copy (or the format you choose). The copy is generated on the first request and served as a plain file on every request after that. An image committed with the theme and one a client uploaded through the back office an hour ago are treated exactly the same way.

```blade
<img src="{{ glide($post->cover, ['w' => 1200]) }}" alt="{{ $post->title }}">
```

## Why use it

Images are usually the heaviest part of a page. Stock photos arrive at 5,000 pixels wide. Client photos arrive as whatever the phone or camera produced, often 5 to 10 MB each. Without a tool, each one has to be opened, cropped, resized to the widths the layout uses, exported and committed by hand.

Uploads are the harder case. After handover, a client uploads a photo straight off their phone, and the page becomes several megabytes heavier than the one you delivered. Nothing breaks, the page still looks right, but it loads slower. The largest image at the top of a page is usually its **Largest Contentful Paint** (LCP, the Core Web Vital Google uses to measure how fast a page loads), so search rankings drift down one upload at a time.

With `glide()`, the size is written in the Blade template, next to the markup that displays the image. The original file is never touched. Every image is optimized on its way to the page, whoever supplied it.

[League's Glide](https://glide.thephpleague.com/) does the image work, but on its own it gives you an image to save, not a URL to put in a `src` attribute. This package adds that last step. The idea comes from the Glide tag in Statamic.

## Features

- 🚀 **On-the-fly image manipulation** - Resize, crop, and transform images dynamically
- 💾 **Automatic caching** - Generated images are cached to avoid regeneration
- 🔧 **Configurable defaults** - Set global parameters for consistent image processing
- 📁 **Multiple source support** - Works with storage/ and public/ directories
- 🌐 **External URL handling** - Passes through external URLs unchanged
- ⚡ **Performance optimized** - Only processes when necessary

## Requirements

| Requirement | Why |
| --- | --- |
| PHP 8.1 or later | Minimum version of the package and of Glide 3, which it uses. |
| Laravel 10 or later | Tested on Laravel 10 (PHP 8.1), 12 and 13 (PHP 8.4). Laravel 11 was not tested, but it sits between two tested versions and uses the same APIs. |
| PHP **GD** extension | Glide uses it to read and write images. To use **Imagick** instead, set `driver` to `imagick` in the config. |
| A local `public` disk, linked with `php artisan storage:link` | Generated images are saved on the `public` disk and served from `/storage`. Without the link, their URLs return 404. |

[league/glide](https://github.com/thephpleague/glide) 3 is installed automatically with this package.

## Installation

Install the package via Composer:

```bash
composer require mehdismekouar/laravel-glide-helper
```

Create the `public/storage` link, once per environment, if it is not already there:

```bash
php artisan storage:link
```

Optionally, publish the config file to change the defaults:

```bash
php artisan vendor:publish --tag="glide-helper-config"
```

## Usage

The signature is:

```php
glide(string $src, array $params = []): string
```

`$params` uses Glide's own short names: `w` and `h` for the size, `fit` for how the image fills that box, `fm` for the format, `q` for the quality. Anything the [Glide documentation](https://glide.thephpleague.com/3.0/api/quick-reference/) lists works here unchanged.

### Images that ship with the theme

For an image that lives in the repository, pass its public URL: `Vite::asset()` for images under `resources/`, `asset()` for images under `public/`. It works the same inside an inline style.

```blade
{{-- An image under resources/, bundled by Vite --}}
<img src="{{ glide(Vite::asset('resources/images/home/desert-sunset.jpg'), ['w' => 800]) }}"
     width="800" height="533" alt="Sunset over the dunes" loading="lazy">

{{-- An image under public/ --}}
<section style="background-image: url('{{ glide(asset('images/hero-bg.jpg'), ['w' => 1600, 'q' => 75]) }}')">
    ...
</section>
```

The original stays in the repository at full resolution. The day the design needs a larger version, you change one number instead of looking for the source photo.

During `npm run dev`, `Vite::asset()` points at the Vite dev server, which is on another domain. The helper returns it unchanged, so you see the original locally and the optimized copy after `npm run build`.

### Images a client uploads through the back office

Whatever stores the upload (a plain file input, Filament, Spatie Media Library) ends up holding a path on the `public` disk or a URL under `/storage`. `glide()` handles both:

```blade
{{-- A path stored by $request->file('cover')->store('posts', 'public') --}}
<img src="{{ glide($post->cover, ['w' => 1200, 'h' => 630, 'fit' => 'crop']) }}"
     width="1200" height="630" alt="{{ $post->title }}">

{{-- A URL from Spatie Media Library --}}
<img src="{{ glide($circuit->getFirstMediaUrl('featured-image'), ['w' => 500]) }}"
     width="500" alt="{{ $circuit->name }}" loading="lazy">
```

The client can upload an 8 MB photo straight off their phone. The page gets an optimized WebP at the width the layout draws, and nobody on the team is involved.

### Responsive images with `srcset`

Each call produces one size, so a `srcset` is the same call at several widths. The browser picks the smallest one that fills the slot:

```blade
@php
    $srcset = collect([480, 800, 1200, 1600])
        ->map(fn ($width) => glide($product->image, ['w' => $width]).' '.$width.'w')
        ->implode(', ');
@endphp

<img src="{{ glide($product->image, ['w' => 800]) }}"
     srcset="{{ $srcset }}"
     sizes="(min-width: 1024px) 50vw, 100vw"
     alt="{{ $product->name }}" loading="lazy">
```

Keep `loading="lazy"` off the image at the top of the page. That one is usually the LCP element, and it needs `loading="eager"` and `fetchpriority="high"` instead. A lazy hero image gives back everything the resize just saved.

### What you can pass, and what you get back

| You pass | Example | Result |
| --- | --- | --- |
| A path on the `public` disk | `posts/cover.jpg` | URL of the optimized copy |
| A URL under your app's `/storage` | `https://example.com/storage/12/photo.jpg` | URL of the optimized copy |
| A URL or path of a file under `public/` | `asset('images/hero.jpg')`, `/images/hero.jpg` | URL of the optimized copy |
| A URL on another domain (CDN, S3, Vite dev server) | `https://cdn.example.com/photo.jpg` | The same URL, unchanged |
| A file that does not exist | `posts/deleted.jpg` | The same value, unchanged. No exception is thrown. |

The optimized copy's URL looks like `https://example.com/storage/manipulated/3f2a9c…e81.webp`.

### Common parameters

| Parameter | What it does | Example |
| --- | --- | --- |
| `w`, `h` | Width and height in pixels. | `['w' => 800]` |
| `fit` | How the image fills the `w` × `h` box: `max` fits inside it and never enlarges, `crop` fills it exactly and cuts the overflow. Also `contain`, `fill`, `fill-max`, `stretch`. | `['w' => 200, 'h' => 200, 'fit' => 'crop']` |
| `fm` | Output format: `webp`, `jpg`, `pjpg` (progressive JPEG), `png`, `gif`. `avif`, `tiff` and `heic` also work if your GD or Imagick build supports them. | `['fm' => 'jpg']` |
| `q` | Quality, from 1 to 100. Lower means a lighter file. | `['q' => 75]` |
| `blur`, `sharp` | Blur or sharpen, from 0 to 100. | `['blur' => 5]` |
| `filt` | Filter: `greyscale` or `sepia`. | `['filt' => 'greyscale']` |
| `mark` | Watermark: the absolute path to an image, placed with `markw` (its width) and `markpos` (e.g. `bottom-right`). | `['mark' => public_path('logo.png'), 'markw' => 100]` |

## Configuration

The defaults are applied to every call, and your parameters override them. They are why a bare `glide($src, ['w' => 800])` produces a WebP at quality 90. Change them in the published `config/glide-helper.php` rather than call by call:

```php
return [
    'defaults' => [
        'q' => 90,      // quality, 1-100
        'fm' => 'webp', // output format
        'fit' => 'max', // fit inside the box, never upscale
    ],
    'output_dir' => 'manipulated', // folder on the public disk for generated images
    'driver' => 'gd', // gd or imagick
];
```

| Option | What it does | Default |
| --- | --- | --- |
| `defaults` | Parameters applied to every call. Any parameter you pass to `glide()` replaces the default of the same name. | `q` 90, `fm` webp, `fit` max |
| `output_dir` | Folder on the `public` disk where generated images are saved. | `manipulated` |
| `driver` | PHP extension used to process images. Use `imagick` only if the Imagick extension is installed. | `gd` |

For large background images, where nobody inspects the detail, a per-call `'q' => 75` is usually indistinguishable on screen and noticeably lighter.

## How it works

On each call, the helper:

1. Merges your parameters over the defaults in `config/glide-helper.php`.
2. Returns a URL on another domain untouched.
3. Looks for the file on the `public` disk, then under `public/`.
4. Returns what you passed in if it finds nothing. A missing file renders as the broken image it already was, instead of an exception taking the page down.
5. Names the output after an MD5 hash of the file's path, its modification time and the parameters, and generates the image only if that file does not exist yet.
6. Returns the public URL of the generated file.

Because the modification time is part of the name, a client who replaces a photo with a new file of the same name gets a new version on the next request, with no cache to clear.

After the first request, the image is a static file. The web server serves it straight off disk, and on each page render PHP only computes a hash and checks that a file exists.

## Known limits

- **The first request for each size pays for generating it.** A very large original can add a noticeable delay to that one page load. Every request after it is served as a static file.
- **Old versions are never deleted.** Replacing a file or changing parameters produces a new name and leaves the previous output in `storage/app/public/manipulated` (or your `output_dir`). On a site with a lot of uploads, that folder needs an occasional clean-out. Emptying it is safe: everything regenerates on demand.
- **Only local files are processed.** Images on S3 or any other remote disk pass through unchanged.

## Troubleshooting

| What you see | What to do |
| --- | --- |
| The generated image URL (`/storage/manipulated/...`) returns 404 | Run `php artisan storage:link`. |
| The original URL comes back, not resized | The file was not found, or it is on another domain. Check the path is on the `public` disk or under `public/`. With `Vite::asset()`, this is expected during `npm run dev`. |
| `GD PHP extension must be installed to use this driver.` | Enable the `gd` extension in your `php.ini`, or set `driver` to `imagick` in the config. |
| `Imagick PHP extension must be installed to use this driver.` | Install the Imagick extension, or set `driver` back to `gd`. |
| `vendor:publish` publishes nothing | Use the tag `glide-helper-config`. |

## Upgrading from 1.x

Version 2 uses [league/glide](https://github.com/thephpleague/glide) 3 directly, instead of going through [spatie/laravel-glide](https://github.com/spatie/laravel-glide). spatie/laravel-glide is tied to an older Glide that cannot be installed next to Guzzle 8, which new Laravel 13 applications use. Your `glide()` calls and the URLs they return do not change.

1. Update the package: `composer require mehdismekouar/laravel-glide-helper:^2.0`.
2. If you set `driver` to `imagick` in `config/laravel-glide.php`, set it in `config/glide-helper.php` instead. If you published the config before, add the `'driver' => 'imagick'` line to it yourself.
3. If your own code uses `Spatie\Glide\GlideImage`, require `spatie/laravel-glide` in your application, as this package no longer installs it.

## Credits

- [Mehdi Mekouar](https://github.com/mehdismekouar)
- Built on top of [League Glide](https://glide.thephpleague.com/)
- Read the story behind it: [Laravel Glide Helper: on-the-fly image optimization in Blade](https://mehdimekouar.com/articles/laravel-glide-helper-image-optimization)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
