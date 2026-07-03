@extends('layouts.app')

@section('title', 'العملاء')
@section('page-title', 'العملاء')

@section('content')

<div class="page-head">
  <div><h3>العملاء</h3><p>قائمة جميع العملاء ومشاريعهم</p></div>
  <a href="{{ route('clients.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    عميل جديد
  </a>
</div>

@if($clients->count())
  <div class="table-card">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الاسم</th>
            <th>الهاتف</th>
            <th>المشاريع</th>
            <th class="num">إجمالي التعاقد</th>
            <th class="num">المحصّل</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($clients as $c)
            @php
              $contract  = $c->projects->sum(fn($p) => $p->initialContractValue());
              $collected = $c->projects->sum(fn($p) => $p->totalCollected());
            @endphp
            <tr class="row-click" onclick="location.href='{{ route('clients.show', $c) }}'">
              <td><strong>{{ $c->name }}</strong></td>
              <td class="muted">{{ $c->phone ?: '—' }}</td>
              <td>
                @foreach($c->projects as $p)
                  <span class="tag gray" style="margin-left:4px">{{ $p->name }}</span>
                @endforeach
              </td>
              <td class="num">{{ number_format($contract) }}</td>
              <td class="num" style="color:var(--pos)">{{ number_format($collected) }}</td>
              <td>
                <a href="{{ route('clients.show', $c) }}" class="btn ghost sm" onclick="event.stopPropagation()">تفاصيل</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
    <h4>لا يوجد عملاء بعد</h4>
    <p><a href="{{ route('clients.create') }}">أضف عميلاً الآن</a></p>
  </div>
@endif

@endsection
