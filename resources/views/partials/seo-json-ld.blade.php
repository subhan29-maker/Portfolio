@php
    $siteUrl = config('app.url', url('/'));
    $person = [
        '@type' => 'Person',
        'name' => $profile['name'] ?? null,
        'url' => $siteUrl,
        'jobTitle' => $profile['role'] ?? 'Full-Stack Developer',
    ];
    if (filled($profile['email'] ?? null)) {
        $person['email'] = $profile['email'];
    }
    if (filled($profile['github'] ?? null)) {
        $person['sameAs'] = array_filter([$profile['github'], $profile['linkedin'] ?? null]);
    }
    $website = [
        '@type' => 'WebSite',
        'name' => ($profile['name'] ?? 'Portfolio').' — '.($profile['role'] ?? 'Portfolio'),
        'url' => $siteUrl,
        'author' => ['@id' => $siteUrl.'#person'],
    ];
    $webpage = [
        '@type' => 'WebPage',
        'name' => trim(View::hasSection('title') ? View::yieldContent('title').' — '.($profile['name'] ?? '') : ($profile['name'] ?? '').' — '.($profile['role'] ?? '')),
        'url' => url()->current(),
        'description' => trim(View::yieldContent('meta_description') ?: ($profile['name'].' is a full-stack developer building responsive web applications.')),
    ];
    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_merge(['@id' => $siteUrl.'#person'], array_filter($person)),
            array_merge(['@id' => $siteUrl.'#website'], array_filter($website)),
            array_merge(['@type' => 'WebPage'], array_filter($webpage)),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
