@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'wrapperClass' => '',
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([
        filled($hint) ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');

    $selected = old($name, $value);

    // Enum casts (like the project status) arrive as objects; compare them
    // by their stored value, exactly like plain strings.
    $selected = $selected instanceof \BackedEnum ? $selected->value : $selected;
@endphp

<div class="field {{ $hasError ? 'field--invalid' : '' }} {{ $wrapperClass }}">
    <label class="field__label" for="{{ $name }}">
        {{ $label }}
        @unless ($required)
            <span class="field__optional">optional</span>
        @endunless
    </label>

    <div class="field__select">
        <select
            class="field__control field__control--select"
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" @endif
            @if (filled($describedBy)) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes }}
        >
            @if (filled($placeholder))
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
        <x-icon name="chevron-right" class="field__select-icon" />
    </div>

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
