@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Latest News Headlines</h4>
    </div>

    <div class="card-body">

        <textarea id="headlineText"
                  class="form-control"
                  rows="15">{{ $message }}</textarea>

        <div class="mt-3 d-flex gap-2">

            <button class="btn btn-primary" onclick="copyHeadlines()">
                📋 Copy Headlines
            </button>

            <a href="https://wa.me/?text={{ urlencode($message) }}"
               target="_blank"
               class="btn btn-success">
                🟢 Share to WhatsApp
            </a>

        </div>

    </div>
</div>

<script>
function copyHeadlines() {
    const text = document.getElementById('headlineText');

    navigator.clipboard.writeText(text.value)
        .then(() => {
            alert('Headlines copied successfully!');
        })
        .catch(err => {
            console.error(err);
        });
}
</script>
@endsection