@props([
    'name',
    'label',
    'hint' => null,
    'checked' => false,
    'value' => '1',
])

<div class="admin-checkbox">
    <input
        type="checkbox"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @if ($checked) checked @endif
        {{ $attributes }}
    >

    <label class="admin-checkbox__label" for="{{ $name }}">
        {{ $label }}
        @if (filled($hint))
            <span class="admin-checkbox__hint">{{ $hint }}</span>
        @endif
    </label>
</div>
