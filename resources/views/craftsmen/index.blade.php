@extends('layouts.app')
@section('title', 'الصنايعية ومستحقاتهم')
@section('page-title', 'الصنايعية ومستحقاتهم')

@section('content')
<div class="page-head">
  <div><h3>الصنايعية ومستحقاتهم</h3><p>كل صنايعي مجمّع عبر كل المشاريع — المتعاقد عليه، المدفوع، والمتبقي المستحق دلوقتي</p></div>
</div>

<div class="grid cols-3" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">عدد الصنايعية</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg></span></div>
    <div class="val tnum">{{ $craftsmen->count() }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المدفوع</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($totalPaid) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المستحق للصنايعية</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span></div>
    <div class="val tnum" style="color:{{ $totalRemaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($totalRemaining) }} <small>ج.م</small></div>
  </div>
</div>

<form method="GET" class="filter-bar">
  @include('partials._sort-select', ['options' => [
    'remaining_desc'  => 'الأعلى مستحقًا',
    'paid_desc'       => 'الأعلى مدفوعًا',
    'contracted_desc' => 'الأعلى تعاقدًا',
    'projects_desc'   => 'الأكثر مشاريع',
    'name'            => 'أبجديًا',
  ]])
</form>

@forelse($craftsmen as $c)
  <div class="table-card" style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid var(--line)">
      <div>
        <h4 style="margin:0">{{ $c->name }}</h4>
        <div class="muted" style="font-size:13px;margin-top:4px">
          @if($c->specialties->count()){{ $c->specialties->join('، ') }} · @endif
          @if($c->phones->count()){{ $c->phones->join(' / ') }} · @endif
          {{ $c->projects }} مشروع · {{ $c->assignments->count() }} بند
        </div>
      </div>
      <div style="display:flex;gap:20px;text-align:center">
        <div><div class="muted" style="font-size:12px">متعاقد</div><div class="tnum" style="font-weight:700">{{ \App\Support\Money::format($c->contracted) }}</div></div>
        <div><div class="muted" style="font-size:12px">مدفوع</div><div class="tnum" style="font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($c->paid) }}</div></div>
        <div><div class="muted" style="font-size:12px">متبقي</div><div class="tnum" style="font-weight:700;color:{{ $c->remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($c->remaining) }}</div></div>
      </div>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>البند</th>
            <th>التعاقد</th>
            <th class="num">متعاقد</th>
            <th class="num">مدفوع</th>
            <th class="num">متبقي</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($c->assignments as $a)
            @php $paid = $a->paidTotal(); $remaining = $a->remaining(); @endphp
            <tr>
              <td>{{ $a->band?->project?->name ?? '—' }}</td>
              <td class="muted">{{ $a->band?->name ?? '—' }}</td>
              <td class="muted">
                {{ $a->contractTypeAr() }}
                @if(in_array($a->contract_type, ['per_meter','per_piece','daily']) && $a->contract_qty)
                  <span style="font-size:12px">({{ rtrim(rtrim(number_format($a->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($a->contract_unit_rate) }})</span>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($a->amount) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
              <td class="num" style="color:{{ $remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($remaining) }}</td>
              <td><a href="{{ route('workers.payments', $a) }}" class="btn ghost sm">الدفعات</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@empty
  <div class="table-card">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
      <h4>لا يوجد صنايعية مسجلين بعد</h4>
    </div>
  </div>
@endforelse

@endsection
