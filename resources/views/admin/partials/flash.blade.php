@if (session('success'))
    <div class="alert alert--success" role="status">
        <svg class="i"><use href="#i-check"/></svg>
        <div>{{ session('success') }}</div>
        <button type="button" class="alert__close" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert--error" role="alert">
        <div>
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="alert__close" aria-label="Dismiss">&times;</button>
    </div>
@endif
