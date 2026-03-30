@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rr-alert-success']) }}>
        {{ $status }}
    </div>
@endif
