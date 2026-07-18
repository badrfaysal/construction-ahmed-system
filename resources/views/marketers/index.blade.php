@extends('layouts.app')
@section('title', 'المسوقين')
@section('page-title', 'إدارة المسوقين')

@section('content')
<div class="page-head">
  <div>
    <h3>المسوقين</h3>
    <p>إدارة بيانات المسوقين والعمولات الخاصة بهم</p>
  </div>
  <button type="button" class="btn" onclick="document.getElementById('add-marketer-modal').classList.add('open')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    إضافة مسوق
  </button>
</div>

@if($errors->any())
  <div class="flash error" style="margin-bottom:16px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<div class="table-card">
  @if($marketers->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الاسم</th>
            <th>رقم الموبايل</th>
            <th class="num">عدد المشاريع</th>
            <th class="num">إجمالي العمولات</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($marketers as $marketer)
            <tr>
              <td><strong>{{ $marketer->name }}</strong></td>
              <td class="muted" style="direction:ltr;text-align:right">{{ $marketer->phone ?: '—' }}</td>
              <td class="num">{{ $marketer->projectsCount() }}</td>
              <td class="num" style="color:var(--pos);font-weight:700">{{ \App\Support\Money::format($marketer->totalPaid()) }}</td>
              <td>
                <div style="display:flex;gap:4px">
                  <form method="POST" action="{{ route('marketers.destroy', $marketer) }}" onsubmit="return confirm('تأكيد حذف المسوق؟')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn ghost sm" style="color:var(--neg)">حذف</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      <h4>لا يوجد مسوقين مسجلين بعد</h4>
    </div>
  @endif
</div>

<div class="modal-overlay" id="add-marketer-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-head">
      <h4 style="margin:0">إضافة مسوق جديد</h4>
      <button class="btn ghost sm" onclick="document.getElementById('add-marketer-modal').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('marketers.store') }}">
      @csrf
      <div class="modal-body">
        <div class="field">
          <label>اسم المسوق *</label>
          <input type="text" name="name" required autofocus>
        </div>
        <div class="field" style="margin-bottom:0">
          <label>رقم الموبايل</label>
          <input type="text" name="phone">
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn pos">إضافة</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('add-marketer-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
</div>
@endsection
