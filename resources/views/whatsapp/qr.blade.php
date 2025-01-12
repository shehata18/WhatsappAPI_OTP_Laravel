@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Scan QR Code</div>
        <div class="card-body text-center">
            @if($qrCode)
                <img src="{{ $qrCode }}" alt="WhatsApp QR Code">
                <p class="mt-3">Scan this QR code with WhatsApp on your phone</p>
                <div id="connection-status" class="alert alert-info">
                    Waiting for connection...
                </div>
            @else
                <div class="alert alert-danger">
                    Failed to generate QR code
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkConnection() {
    fetch('{{ route("whatsapp.check-connection") }}')
        .then(response => response.json())
        .then(data => {
            if (data.connected) {
                document.getElementById('connection-status').className = 'alert alert-success';
                document.getElementById('connection-status').textContent = 'Connected successfully!';
                window.location.href = '{{ route("dashboard") }}';
            }
        });
}

// Check connection status every 5 seconds
setInterval(checkConnection, 5000);
</script>
@endpush
@endsection 