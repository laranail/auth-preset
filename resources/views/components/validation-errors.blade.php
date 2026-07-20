@props(['errors' => []])

@if (! empty($errors))
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.375rem; padding: 0.75rem; margin-bottom: 1rem;">
        @foreach ($errors->all() as $error)
            <p class="error">{{ $error }}</p>
        @endforeach
    </div>
@endif
