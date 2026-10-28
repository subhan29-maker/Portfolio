@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'rows' => 6,
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([
        filled($hint) ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div class="field {{ $hasError ? 'field--invalid' : '' }}">
    <label class="field__label" for="{{ $name }}">
        {{ $label }}
        @unless ($required)
            <span class="field__optional">optional</span>
        @endunless
    </label>

    <textarea
        class="field__control field__control--textarea"
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        @if (filled($placeholder)) placeholder="{{ $placeholder }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if (filled($describedBy)) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>

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
