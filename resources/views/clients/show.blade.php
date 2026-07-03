@extends('layouts.app')
@section('title', $client->name)
@section('page-title', $client->name)

@section('content')
<div class="page-head">
  <div>
    <h3>{{ $client->name }}</h3>
    <p>{{ $client->phone }} @if($client->address) — {{ $client->address }} @endif</p>
  </div>
  <div class="btn-row">
    <a href="{{ route('clients.edit', $client) }}" class="btn ghost">تعديل</a>
    <a href="{{ route('clients.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

{{-- Projects for this client --}}
@if($client->projects->count())
  <div class="pcards">
    @foreach($client->projects as $p)
      <a class="pcard {{ $p->status === 'done' ? 'is-done' : '' }}" href="{{ route('projects.show', $p) }}">
        <div class="pc-band"></div>
        <div class="pc-body">
          <div class="pc-head">
            <div class="pc-name">{{ $p->name }}</div>
            <span class="tag {{ $p->status === 'done' ? 'green' : 'blue' }}">{{ $p->status === 'done' ? 'مكتمل' : 'جاري' }}</span>
          </div>
          <div class="pc-fin">
            <div><div class="l">التعاقد</div><div class="v">{{ number_format($p->initialContractValue()) }}</div></div>
            <div><div class="l">محصّل</div><div class="v" style="color:var(--pos)">{{ number_format($p->totalCollected()) }}</div></div>
          </div>
        </div>
      </a>
    @endforeach
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    <h4>لا توجد مشاريع لهذا العميل</h4>
    <p><a href="{{ route('projects.create') }}">أضف مشروعاً</a></p>
  </div>
@endif
@endsection
