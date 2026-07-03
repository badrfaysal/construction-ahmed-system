@extends('layouts.app')
@section('title', 'تقرير الفنيين')
@section('page-title', 'تقرير الفنيين والفرق')

@section('content')
<div class="page-head">
  <div><h3>تقرير الفنيين والفرق</h3><p>إجمالي الأجور المدفوعة لكل فريق عبر كل المشاريع</p></div>
</div>

<div class="table-card">
  @if($technicians->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الفريق / الفني</th>
            <th class="num">عدد البنود</th>
            <th class="num">إجمالي الأجور</th>
            <th class="num">إجمالي قيمة البنود للعميل</th>
          </tr>
        </thead>
        <tbody>
          @foreach($technicians as $t)
            <tr>
              <td><strong>{{ $t->team_name }}</strong></td>
              <td class="num">{{ $t->bands_count }}</td>
              <td class="num">{{ number_format($t->total_labor) }}</td>
              <td class="num">{{ number_format($t->total_client_price) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td>الإجماليات</td>
            <td class="num">{{ $technicians->sum('bands_count') }}</td>
            <td class="num">{{ number_format($technicians->sum('total_labor')) }}</td>
            <td class="num">{{ number_format($technicians->sum('total_client_price')) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد بيانات فرق مسجلة بعد</h4></div>
  @endif
</div>
@endsection
