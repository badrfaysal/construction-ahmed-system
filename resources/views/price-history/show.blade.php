@extends('layouts.app')
@section('title', $itemName)
@section('page-title', 'سجل أسعار: ' . $itemName)

@section('content')
<div class="page-head">
  <div>
    <h3>{{ $itemName }}</h3>
    <p>
      كل عمليات الشراء الفعلية المسجلة لهذا الصنف
      @if(count($variants) > 1)
        — يشمل الصيغ: {{ implode('، ', $variants) }}
      @endif
    </p>
  </div>
  <a href="{{ route('price-history.index') }}" class="btn ghost">رجوع</a>
</div>

<div class="table-card">
  @if($purchases->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>التاريخ</th><th>المشروع</th><th>المورد</th><th class="num">الكمية</th><th class="num">سعر الوحدة</th><th class="num">التغير</th></tr></thead>
        <tbody>
          @php $prev = null; @endphp
          @foreach($purchases as $m)
            @php
              $change = $prev ? round((($m->unit_price - $prev->unit_price) / $prev->unit_price) * 100, 1) : null;
              $prev = $m;
            @endphp
            <tr>
              <td class="muted">{{ $m->date->format('Y-m-d') }}</td>
              <td>{{ $m->project?->name ?? '—' }}</td>
              <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
              <td class="num">{{ number_format($m->qty, 1) }}</td>
              <td class="num"><strong>{{ number_format($m->unit_price, 2) }}</strong></td>
              <td class="num">
                @if(is_null($change))
                  <span class="muted">—</span>
                @elseif($change > 0)
                  <span style="color:var(--neg)">▲ {{ $change }}%</span>
                @elseif($change < 0)
                  <span style="color:var(--pos)">▼ {{ abs($change) }}%</span>
                @else
                  <span class="muted">0%</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد سجلات</h4></div>
  @endif
</div>
@endsection
