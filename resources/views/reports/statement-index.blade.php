@extends('layouts.app')
@section('title', 'كشف حساب العميل')
@section('page-title', 'كشف حساب العميل')

@section('content')
<div class="page-head">
  <div><h3>كشف حساب العميل</h3><p>اختر مشروعاً لعرض كشف حساب تفصيلي قابل للطباعة</p></div>
</div>

<div class="table-card">
  @if($projects->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>العميل</th>
            <th class="num">إجمالي المستحق</th>
            <th class="num">المحصّل</th>
            <th class="num">المتبقي</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $project)
            <tr>
              <td><strong>{{ $project->name }}</strong></td>
              <td class="muted">{{ $project->client->name }}</td>
              <td class="num">{{ \App\Support\Money::format($project->cached_actual_total) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($project->total_paid) }}</td>
              <td class="num" style="color:{{ $project->balance > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($project->balance) }}</td>
              <td style="white-space:nowrap">
                <a href="{{ route('reports.statement', $project) }}" class="btn ghost sm">تفصيلي</a>
                <a href="{{ route('reports.statement.summary', $project) }}" class="btn ghost sm">مختصر</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد مشاريع بعد</h4></div>
  @endif
</div>
@endsection
