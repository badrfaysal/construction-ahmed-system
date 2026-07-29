@extends('layouts.app')
@section('title', 'الإعدادات')
@section('page-title', 'الإعدادات العامة')

@section('content')
<div class="page-head">
  <div><h3>الإعدادات</h3><p>القيم دي بتتطبق على كل النظام — عروض الأسعار، تسجيل الخامات والمصنعية، والمستندات المطبوعة</p></div>
</div>

<div class="tabs" style="margin-bottom:24px">
  <a class="tab active" style="cursor:pointer" onclick="switchTab('general', this)">الإعدادات العامة</a>
  <a class="tab" style="cursor:pointer" onclick="switchTab('accounts', this)">الحسابات البنكية والمحافظ</a>
  <a class="tab" style="cursor:pointer" onclick="switchTab('database', this)">النسخ الاحتياطي</a>
</div>

{{-- Tab 1: General Settings --}}
<div id="tab-general" class="tab-pane" style="display:block">
  <form method="POST" action="{{ route('settings.update') }}" style="max-width:640px">
    @csrf
    @method('PUT')

    <div class="form-card">
      <div class="section-label">بيانات الشركة (تظهر في عروض الأسعار وكشوف الحساب المطبوعة)</div>
      <div class="field">
        <label>اسم الشركة *</label>
        <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required>
      </div>
      <div class="field">
        <label>الوصف / التخصص</label>
        <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings->company_tagline) }}" placeholder="مقاولات وتشطيبات · القاهرة">
      </div>
      <div class="row2">
        <div class="field">
          <label>الهاتف</label>
          <input type="text" name="company_phone" value="{{ old('company_phone', $settings->company_phone) }}">
        </div>
      </div>
    </div>

    <div class="form-card">
      <div class="section-label">إعدادات واتساب</div>
      <div class="field">
        <label>كود الدولة (بدون +) *</label>
        <input type="text" name="whatsapp_country_code" value="{{ old('whatsapp_country_code', $settings->whatsapp_country_code) }}" maxlength="5" required style="max-width:120px">
        <p class="muted" style="margin-top:6px">مصر = 20. بيتضاف تلقائياً قبل رقم العميل عند إنشاء رابط واتساب من عرض السعر.</p>
      </div>
    </div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn pos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ الإعدادات</button>
    </div>
  </form>
</div>

{{-- Tab 2: Accounts --}}
<div id="tab-accounts" class="tab-pane" style="display:none">
  <div style="max-width:800px">
    <div class="form-card">
      <div class="section-label">حسابات ومحافظ المقاولات</div>
      <div style="margin-bottom:20px;">
          <table class="table">
              <thead>
                  <tr>
                      <th style="text-align: right">اسم الحساب</th>
                      <th style="text-align: right">التصنيف</th>
                      <th style="text-align: right">رصيد الافتتاح</th>
                      <th style="text-align: right">الرصيد الحالي</th>
                  </tr>
              </thead>
              <tbody>
                  @forelse($accounts ?? [] as $acc)
                  <tr>
                      <td>{{ $acc->name }}</td>
                      <td>
                          @if($acc->category === 'bank_wallet') بنك / محفظة
                          @elseif($acc->category === 'safe_cash') خزينة نقدية
                          @else مقاولات ومشاريع @endif
                      </td>
                      <td>{{ \App\Support\Money::format($acc->initial_balance) }}</td>
                      <td style="font-weight:bold; color:var(--brand)">{{ \App\Support\Money::format($acc->balance) }}</td>
                  </tr>
                  @empty
                  <tr><td colspan="4" class="text-center">لا توجد حسابات مضافة.</td></tr>
                  @endforelse
              </tbody>
          </table>
      </div>

      <div style="border-top:1px solid var(--line); padding-top:20px">
        <h4 style="margin-bottom:16px;font-size:14px;color:var(--ink-1)">إضافة حساب جديد</h4>
        <form method="POST" action="{{ route('settings.store_account') }}">
          @csrf
          <div class="row2">
            <div class="field">
              <label>اسم الحساب / المحفظة *</label>
              <input type="text" name="name" required placeholder="مثال: انستاباي أحمد">
            </div>
            <div class="field">
              <label>التصنيف *</label>
              <select name="category" required>
                <option value="bank_wallet">بنك / محفظة إلكترونية</option>
                <option value="safe_cash">خزينة نقدية</option>
                <option value="project_sector">مقاولات عامة</option>
              </select>
            </div>
            <div class="field">
              <label>رصيد الافتتاح *</label>
              <input type="number" step="0.01" name="initial_balance" value="0" required min="0">
            </div>
          </div>
          <div class="btn-row" style="margin-top:8px">
            <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>إضافة حساب جديد</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Tab 3: Database Export --}}
<div id="tab-database" class="tab-pane" style="display:none">
  <div style="max-width:640px">
    <div class="form-card">
      <div class="section-label">قاعدة البيانات</div>
      <div class="field">
        <p class="muted" style="margin-bottom:12px; line-height:1.6">يمكنك تحميل نسخة احتياطية من قاعدة البيانات بصيغة (.sql) لضمان أمان البيانات وإمكانية استرجاعها في أي وقت. يرجى حفظ الملف في مكان آمن وعدم مشاركته مع جهات غير موثوقة.</p>
        <form method="POST" action="{{ route('settings.export_db') }}" style="display:inline">
          @csrf
          <button type="submit" class="btn pos" style="background:#10b981; border-color:#059669"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" style="margin-inline-end:6px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> تصدير قاعدة البيانات</button>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function switchTab(id, el) {
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + id).style.display = 'block';
  el.classList.add('active');
}

// Support switching tabs via URL hash (e.g. #accounts)
window.addEventListener('DOMContentLoaded', () => {
  const hash = window.location.hash.replace('#', '');
  if (hash && document.getElementById('tab-' + hash)) {
    const tabEl = Array.from(document.querySelectorAll('.tabs .tab')).find(t => t.getAttribute('onclick').includes(hash));
    if(tabEl) switchTab(hash, tabEl);
  }
});
</script>
@endpush
@endsection
