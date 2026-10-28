@props([
    'src' => null,
    'alt' => '',
    'fallback' => 'images/placeholder.svg',
    'loading' => 'lazy',
    'priority' => false,
])

@php
    /**
     * Resolve a public image path to a URL.
     *
     * Missing files fall back to a generated placeholder, so replacing or
     * renaming a screenshot can never render a broken image.
     */
    /**
     * Resolve a public image path to a URL.
     *
     * Absolute http(s) URLs (for example images served by the Portfolio CRM)
     * are used as they are. Local paths resolve against the public directory,
     * and missing files fall back to a generated placeholder, so replacing or
     * renaming a screenshot can never render a broken image.
     */
    $resolve = static function (?string $path, string $default): string {
        $path = trim((string) $path);

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $path = ltrim($path, '/');

        if ($path !== '' && file_exists(public_path($path))) {
            return asset($path);
        }

        return asset($default);
    };

    $placeholderUrl = asset($fallback);
    $resolved = $resolve($src, $fallback);
    $isFallback = $resolved === $placeholderUrl;
@endphp

<img
    src="{{ $resolved }}"
    @unless ($isFallback) data-fallback="{{ $placeholderUrl }}" @endunless
    alt="{{ $alt }}"
    loading="{{ $priority ? 'eager' : $loading }}"
    decoding="{{ $priority ? 'sync' : 'async' }}"
    @if ($priority) fetchpriority="high" @endif
    {{ $attributes->merge(['class' => 'media__image']) }}
>
