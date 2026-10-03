@extends('layouts.app')
@section('page-title', 'Raise emergency')
@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
<x-page-header title="Raise emergency request" subtitle="Use only for critical, time-sensitive incidents." sys="EMERGENCY://RESPONSE" />
<div class="term-panel p-6">
    <form method="POST" action="{{ route('portal.emergency.store') }}" class="space-y-4">@csrf
        <div>
            <label class="term-field-label">Severity</label>
            <select name="severity" class="term-input"><option>CRITICAL</option><option>HIGH</option><option selected>NORMAL</option></select>
        </div>
        <div>
            <label class="term-field-label">Category</label>
            <input name="category" value="incident" class="term-input" maxlength="100" />
        </div>
        <div>
            <label class="term-field-label">Description</label>
            <textarea name="description" required rows="4" minlength="10" maxlength="2000" class="term-input"></textarea>
        </div>
        <button class="btn btn-destructive btn-sm">Submit emergency</button>
    </form>
</div>
</div>
@endsection
