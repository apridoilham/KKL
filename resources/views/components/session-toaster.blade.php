@if (session()->has('status'))
    <div
        x-data="{}"
        x-init="$dispatch('toast', { status: 'success', message: '{{ session('status') }}' })"
    ></div>
@endif

@if (session()->has('dataSession'))
    <div
        x-data="{}"
        x-init="$dispatch('toast', { status: '{{ session('dataSession')['status'] }}', message: '{{ session('dataSession')['message'] }}' })"
    ></div>
@endif