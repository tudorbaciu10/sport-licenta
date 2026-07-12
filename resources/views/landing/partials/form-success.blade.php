{{-- Terminal success marker: app-shell.js closes the panel + shows the message as a toast --}}
<div data-success @if (! empty($refresh)) data-refresh="{{ $refresh }}" @endif class="panel-success">
    <div class="panel-success__icon">✓</div>
    <p>{{ $message }}</p>
</div>
