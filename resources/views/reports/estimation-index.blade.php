@extends('layouts.app')
@section('title', 'تقدير تكلفة مشروع جديد')
@section('page-title', 'تقدير تكلفة مشروع جديد')

@section('content')
<div class="page-head">
  <div>
    <h3>تقدير تكلفة مشروع جديد</h3>
    <p>اختار مشروع سابق كمرجع (مثلاً شقة 100م) عشان تشوف كل بند اشتغلت فيه وكل خامة اشتريتها له بالتفصيل — تقدير جاهز لأي مشروع جديد بنفس المساحة</p>
  </div>
</div>

<div class="table-card">
  @if($projects->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>العميل</th>
            <th class="num">المساحة</th>
            <th>الحالة</th>
            <th class="num">عدد البنود</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $p)
            <tr class="row-click" onclick="window.location='{{ route('reports.estimation.show', $p) }}'">
              <td><strong>{{ $p->name }}</strong></td>
              <td class="muted">{{ $p->client->name ?? '—' }}</td>
              <td class="num">{{ $p->area ? rtrim(rtrim($p->area, '0'), '.') . ' م²' : '—' }}</td>
              <td>
                <span class="tag {{ $p->status === 'done' ? 'green' : 'blue' }}">{{ $p->status === 'done' ? 'منتهي' : 'جاري' }}</span>
              </td>
              <td class="num">{{ $p->bands_count }}</td>
              <td>
                <a href="{{ route('reports.estimation.show', $p) }}" class="btn ghost sm">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
                  عرض التقدير
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
      <h4>لا توجد مشاريع بعد</h4>
      <p>أضف مشروعًا وسجّل بنوده وخاماته عشان يظهر هنا كمرجع للتقدير</p>
    </div>
  @endif
</div>
@endsection
