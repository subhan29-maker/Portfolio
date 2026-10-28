@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'autocomplete' => null,
    'placeholder' => null,
    'inputmode' => null,
    'wrapperClass' => '',
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([
        filled($hint) ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div class="field {{ $hasError ? 'field--invalid' : '' }} {{ $wrapperClass }}">
    <label class="field__label" for="{{ $name }}">
        {{ $label }}
        @unless ($required)
            <span class="field__optional">optional</span>
        @endunless
    </label>

    <input
        class="field__control"
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if (filled($placeholder)) placeholder="{{ $placeholder }}" @endif
        @if (filled($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
        @if (filled($inputmode)) inputmode="{{ $inputmode }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if (filled($describedBy)) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes }}
    >

    @if (filled($hint))
        <p class="field__hint" id="{{ $name }}-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="field__error" id="{{ $name }}-error">
            <x-icon name="alert" class="icon--sm" />
            {{ $message }}
        </p>
    @enderror
</div>
