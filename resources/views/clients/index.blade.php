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
  <div style="margin-bottom:12px;display:flex;align-items:center;gap:8px">
    <label style="font-size:13px;font-weight:600;color:var(--ink-2)">ترتيب حسب:</label>
    <select id="client-sort" onchange="sortClientsTable(this.value)" style="padding:6px 12px;border-radius:8px;border:1px solid var(--line);font-size:13px;background:var(--surface);color:var(--ink);cursor:pointer">
      <option value="newest">الأحدث إضافة</option>
      <option value="oldest">الأقدم إضافة</option>
      <option value="volume_desc">الأعلى تعاملاً</option>
      <option value="volume_asc">الأقل تعاملاً</option>
      <option value="name">الاسم (أ-ي)</option>
    </select>
  </div>

  <div class="table-card">
    <div class="table-scroll">
      <table id="clients-table">
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
            <tr class="row-click" onclick="location.href='{{ route('clients.show', $c) }}'"
                data-created="{{ $c->created_at?->timestamp ?? 0 }}"
                data-volume="{{ $contract }}"
                data-name="{{ $c->name }}">
              <td><strong>{{ $c->name }}</strong></td>
              <td class="muted">{{ $c->phone ?: '—' }}</td>
              <td>
                @foreach($c->projects as $p)
                  <span class="tag gray" style="margin-left:4px">{{ $p->name }}</span>
                @endforeach
              </td>
              <td class="num">{{ \App\Support\Money::format($contract) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($collected) }}</td>
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

@push('scripts')
<script>
function sortClientsTable(mode) {
  const tbody = document.querySelector('#clients-table tbody');
  if (!tbody) return;
  const rows = Array.from(tbody.querySelectorAll('tr'));
  rows.sort((a, b) => {
    switch (mode) {
      case 'newest':
        return (parseFloat(b.dataset.created) || 0) - (parseFloat(a.dataset.created) || 0);
      case 'oldest':
        return (parseFloat(a.dataset.created) || 0) - (parseFloat(b.dataset.created) || 0);
      case 'volume_desc':
        return (parseFloat(b.dataset.volume) || 0) - (parseFloat(a.dataset.volume) || 0);
      case 'volume_asc':
        return (parseFloat(a.dataset.volume) || 0) - (parseFloat(b.dataset.volume) || 0);
      case 'name':
        return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'ar');
      default:
        return 0;
    }
  });
  rows.forEach(r => tbody.appendChild(r));
}
// Default: newest first
document.addEventListener('DOMContentLoaded', () => sortClientsTable('newest'));
</script>
@endpush

@endsection
