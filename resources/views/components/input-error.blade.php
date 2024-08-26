@props(['messages'])

@if ($messages)
    {{-- <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}> --}}
    <ul {{ $attributes->merge(['class' => 'text-danger fs-6']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
