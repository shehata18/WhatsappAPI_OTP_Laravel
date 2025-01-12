@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">WhatsApp Setup</div>
        <div class="card-body">
            <p>Click below to start WhatsApp setup process:</p>
            <form action="{{ route('whatsapp.create') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Create WhatsApp Instance</button>
            </form>
        </div>
    </div>
</div>
@endsection 