{{-- Inline flash messages for the auth cards --}}
@foreach (['status' => 'success', 'message' => 'success', 'success' => 'success', 'warning' => 'warning', 'error' => 'error'] as $flashKey => $flashTone)
    @if (session($flashKey))
        <div class="bm-alert bm-alert--{{ $flashTone }}" role="alert">{{ session($flashKey) }}</div>
    @endif
@endforeach
