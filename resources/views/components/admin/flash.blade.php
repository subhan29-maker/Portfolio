@if (session('status'))
    <div class="alert alert--success" data-flash role="status">
        <x-icon name="check-circle" class="alert__icon" />
        <div>
            <p class="alert__title">Done</p>
            <p class="alert__text">{{ session('status') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert--error" role="alert">
        <x-icon name="alert" class="alert__icon" />
        <div>
            <p class="alert__title">Please check the form</p>
            <p class="alert__text">
                Nothing was saved. Fix the highlighted {{ $errors->count() === 1 ? 'field' : 'fields' }} below and try again.
            </p>
            <ul class="admin-error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
