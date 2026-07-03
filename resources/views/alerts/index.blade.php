@extends('layouts.app')
@section('title', 'التنبيهات')
@section('page-title', 'التنبيهات والمتابعة')

@section('content')
<div class="page-head">
  <div><h3>التنبيهات والمتابعة</h3><p>كل ما يحتاج إجراء سريع اليوم</p></div>
</div>

<div class="grid cols-4" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">أقساط متأخرة</span><span class="ic ic-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span></div>
    <div class="val tnum">{{ $overdueInstallments->count() }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">أقساط قريبة (٧ أيام)</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span></div>
    <div class="val tnum">{{ $upcomingInstallments->count() }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">شكاوى ضمان مفتوحة</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-shield"/></svg></span></div>
    <div class="val tnum">{{ $openComplaints->count() }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">مشاريع بدون حركة (٣٠ يوم)</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg></span></div>
    <div class="val tnum">{{ $staleProjects->count() }}</div>
  </div>
</div>

{{-- Overdue installments --}}
<div class="section-label">أقساط متأخرة عن الاستحقاق</div>
<div class="table-card" style="margin-bottom:24px">
  @if($overdueInstallments->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>البند</th><th>تاريخ الاستحقاق</th><th class="num">المبلغ</th><th></th></tr></thead>
        <tbody>
          @foreach($overdueInstallments as $inst)
            <tr>
              <td><a href="{{ route('projects.show', $inst->project) }}">{{ $inst->project->name }}</a></td>
              <td class="muted">{{ $inst->label }}</td>
              <td><span class="tag red">{{ $inst->due_date->format('Y-m-d') }}</span></td>
              <td class="num">{{ number_format($inst->amount) }}</td>
              <td>
                <form method="POST" action="{{ route('installments.markPaid', $inst) }}">
                  @csrf
                  <button class="btn pos sm">تحصيل</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد أقساط متأخرة 🎉</h4></div>
  @endif
</div>

{{-- Upcoming installments --}}
<div class="section-label">أقساط قادمة خلال ٧ أيام</div>
<div class="table-card" style="margin-bottom:24px">
  @if($upcomingInstallments->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>البند</th><th>تاريخ الاستحقاق</th><th class="num">المبلغ</th></tr></thead>
        <tbody>
          @foreach($upcomingInstallments as $inst)
            <tr>
              <td><a href="{{ route('projects.show', $inst->project) }}">{{ $inst->project->name }}</a></td>
              <td class="muted">{{ $inst->label }}</td>
              <td><span class="tag amber">{{ $inst->due_date->format('Y-m-d') }}</span></td>
              <td class="num">{{ number_format($inst->amount) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد أقساط قريبة</h4></div>
  @endif
</div>

{{-- Expiring warranties --}}
<div class="section-label">ضمانات قاربت على الانتهاء (٣٠ يوم)</div>
<div class="table-card" style="margin-bottom:24px">
  @if($expiringWarranties->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>العميل</th><th>تاريخ الانتهاء</th></tr></thead>
        <tbody>
          @foreach($expiringWarranties as $project)
            <tr>
              <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
              <td class="muted">{{ $project->client->name }}</td>
              <td><span class="tag amber">{{ $project->warranty->expiresAt()->format('Y-m-d') }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد ضمانات قاربت على الانتهاء</h4></div>
  @endif
</div>

{{-- Open warranty complaints --}}
<div class="section-label">شكاوى ضمان لم يتم حلها</div>
<div class="table-card" style="margin-bottom:24px">
  @if($openComplaints->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>الوصف</th><th>التاريخ</th><th>الحالة</th></tr></thead>
        <tbody>
          @foreach($openComplaints as $c)
            <tr>
              <td><a href="{{ route('warranties.show', $c->warranty->project) }}">{{ $c->warranty->project->name }}</a></td>
              <td class="muted">{{ $c->description }}</td>
              <td class="muted">{{ $c->date->format('Y-m-d') }}</td>
              <td><span class="tag amber">{{ $c->status === 'pending' ? 'قيد المتابعة' : $c->status }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد شكاوى مفتوحة</h4></div>
  @endif
</div>

{{-- Stale active projects --}}
<div class="section-label">مشاريع نشطة بدون حركة مالية منذ ٣٠ يوم</div>
<div class="table-card">
  @if($staleProjects->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>العميل</th><th>تاريخ البدء</th></tr></thead>
        <tbody>
          @foreach($staleProjects as $project)
            <tr>
              <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
              <td class="muted">{{ $project->client->name }}</td>
              <td class="muted">{{ $project->start_date?->format('Y-m-d') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>كل المشاريع النشطة عليها حركة حديثة</h4></div>
  @endif
</div>
@endsection
