@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'fw-semibold fs-6 text-info']) }}>
        {{ $status }}
    </div>
@endif

{{-- @props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif --}}
