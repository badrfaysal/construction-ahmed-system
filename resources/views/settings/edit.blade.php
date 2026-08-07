@extends('layouts.app')
@section('title', 'الإعدادات')
@section('page-title', 'الإعدادات العامة')

@section('content')
<div class="page-head">
  <div><h3>الإعدادات</h3><p>القيم دي بتتطبق على كل النظام — عروض الأسعار، تسجيل الخامات والمصنعية، والمستندات المطبوعة</p></div>
</div>

<div class="tabs" style="margin-bottom:24px">
  <a class="tab active" style="cursor:pointer" onclick="switchTab('general', this)">الإعدادات العامة</a>
  <a class="tab" style="cursor:pointer" onclick="switchTab('expenses', this)">بنود المصاريف</a>
  <a class="tab" style="cursor:pointer" onclick="switchTab('users', this)">المستخدمين</a>
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

{{-- Tab 1.2: Expense Categories --}}
<div id="tab-expenses" class="tab-pane" style="display:none">
  <div style="display:flex; gap:24px; flex-wrap:wrap; max-width:960px;">
    
    <!-- Form Side (Now First so it appears on the Right in RTL) -->
    <div style="flex:1; min-width:280px;">
      <div class="form-card" id="cat-form-card" style="margin:0; border:2px solid #e2e8f0; border-radius:12px; transition: border-color 0.3s; background:#f8fafc; padding:20px;">
        <div class="section-label" id="cat-form-title" style="color:#1e293b; display:flex; align-items:center; gap:8px; font-size:1.1rem; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
          <span>إضافة بند مصروف جديد</span>
        </div>
        <form method="POST" action="{{ route('settings.expense_categories.store') }}" id="cat-form">
          @csrf
          <input type="hidden" name="_method" value="POST" id="cat-method">
          <div class="field" style="margin-bottom:16px;">
            <label style="font-weight:700; color:#334155; margin-bottom:6px; display:block;">اسم البند *</label>
            <input type="text" name="name" id="cat-name" required placeholder="مثال: إيجار، صيانة، مرتبات..." style="font-weight:600; padding:10px; border:1px solid #cbd5e1; border-radius:8px; width:100%;">
          </div>
          <div class="field" style="margin-bottom:24px;">
            <label style="font-weight:600; color:#475569; margin-bottom:6px; display:block;">وصف مختصر (اختياري)</label>
            <input type="text" name="description" id="cat-desc" placeholder="توضيح إضافي..." style="padding:10px; border:1px solid #cbd5e1; border-radius:8px; width:100%;">
          </div>
          <button type="submit" class="btn" id="cat-submit" style="width:100%; background:#3b82f6; color:#fff; border:none; border-radius:8px; padding:12px; font-weight:700; margin-bottom:10px; font-size:1rem; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            <span>إضافة البند</span>
          </button>
          <button type="button" class="btn outline" id="cat-cancel" onclick="cancelCatEdit()" style="width:100%; display:none; border:1px solid #94a3b8; color:#64748b; border-radius:8px; padding:10px; font-weight:600; margin-bottom:10px; cursor:pointer; background:transparent; justify-content:center; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            <span>إلغاء التعديل</span>
          </button>
          <a href="{{ route('general_expenses.index') }}" class="btn outline" style="width:100%; display:flex; justify-content:center; align-items:center; gap:6px; border:1px solid #cbd5e1; background:#fff; color:#475569; border-radius:8px; padding:10px; font-weight:600; text-decoration:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            الذهاب للمصروفات بدون إضافة
          </a>
        </form>
      </div>
    </div>

    <!-- Table Side (Now Second so it appears on the Left in RTL) -->
    <div style="flex:2; min-width:300px;">
      <div class="form-card" style="margin:0; height:100%;">
        <div class="section-label" style="display:flex; justify-content:space-between; align-items:center;">
          <div style="display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-list-ul" style="color:var(--accent)"></i> بنود المصروفات الحالية
          </div>
        </div>
        <table class="table" style="margin-top:10px;">
          <thead>
            <tr>
              <th style="width:50px; text-align:center">#</th>
              <th>اسم البند</th>
              <th>الوصف</th>
              <th style="text-align:center; width:80px">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach($expenseCategories as $index => $cat)
            <tr>
              <td style="text-align:center">{{ $index + 1 }}</td>
              <td style="font-weight:600; color:var(--ink-1);"><i class="fa-solid fa-thumbtack" style="color:#3b82f6; margin-inline-end:6px; font-size:12px;"></i> {{ $cat->name }}</td>
              <td class="muted">{{ $cat->description }}</td>
              <td style="text-align:center; padding:8px;">
                <button type="button" class="btn ghost sm" onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}')" style="color:#2563eb; padding:6px 12px; border-radius:6px; background:#eff6ff;" title="تعديل">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" style="margin-inline-end:4px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                  تعديل
                </button>
              </td>
            </tr>
            @endforeach
            @if($expenseCategories->isEmpty())
            <tr>
              <td colspan="4" style="text-align:center" class="muted">لا توجد بنود حالياً</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

{{-- Tab 1.5: Users --}}
<div id="tab-users" class="tab-pane" style="display:none">
  <div style="max-width:900px">
    <div class="form-card">
      <div class="section-label">مستخدمين النظام (خاص بالمقاولات فقط)</div>
      
      <div style="margin-bottom:30px">
        <table class="table">
          <thead>
            <tr>
              <th style="text-align: right">الاسم</th>
              <th style="text-align: right">اسم المستخدم</th>
              <th style="text-align: right">الصلاحية</th>
              <th style="text-align: right">الماليات</th>
              <th style="text-align: right">الحالة</th>
              <th style="text-align: right">آخر دخول</th>
              <th style="text-align: right">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td dir="ltr" style="text-align: right">{{ $user->username }}</td>
              <td>{{ $user->role === 'admin' ? 'مدير' : 'مشاهد' }}</td>
              <td>{{ $user->hide_financials ? 'مخفية' : 'ظاهرة' }}</td>
              <td>
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $user->is_active ? 'var(--green)' : 'var(--red)' }}; margin-inline-end:4px;"></span>
                {{ $user->is_active ? 'نشط' : 'موقوف' }}
              </td>
              <td class="muted">{{ $user->last_login ? $user->last_login->diffForHumans() : '-' }}</td>
              <td>
                <div style="display:flex; gap:8px">
                  <button type="button" class="btn outline" style="padding:4px 8px; font-size:12px" onclick="editUser({{ $user->toJson() }})">تعديل</button>
                  @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('settings.users.delete', $user->id) }}" onsubmit="return confirm('تأكيد الحذف؟')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn outline" style="padding:4px 8px; font-size:12px; color:var(--red); border-color:var(--red)">حذف</button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div style="border-top:1px solid var(--line); padding-top:20px">
        <h4 id="user-form-title" style="margin-bottom:16px;font-size:14px;color:var(--ink-1)">إضافة مستخدم جديد</h4>
        <form id="user-form" method="POST" action="{{ route('settings.users.store') }}">
          @csrf
          <input type="hidden" name="_method" id="user-method" value="POST">
          <div class="row2">
            <div class="field">
              <label>الاسم بالكامل *</label>
              <input type="text" name="name" id="user-name" required placeholder="مثال: أحمد مصطفى">
            </div>
            <div class="field">
              <label>اسم المستخدم (للدخول) *</label>
              <input type="text" name="username" id="user-username" required placeholder="ahmed" dir="ltr" style="text-align: right">
            </div>
          </div>
          <div class="row2">
            <div class="field">
              <label>كلمة المرور</label>
              <input type="text" name="password" id="user-password" placeholder="اتركها فارغة إذا لم ترد التغيير (في حالة التعديل)" minlength="6">
            </div>
            <div class="field">
              <label>الصلاحية *</label>
              <select name="role" id="user-role" required>
                <option value="admin">مدير (صلاحيات كاملة)</option>
                <option value="viewer">مشاهد (للقراءة فقط)</option>
              </select>
            </div>
          </div>
          <div class="row2">
            <div style="display:flex; gap:20px; align-items:center; margin-top:8px">
              <div style="display:flex; align-items:center; gap:6px">
                <input type="checkbox" name="is_active" id="user-active" value="1" checked style="width:auto">
                <label for="user-active" style="margin:0; font-size:13px; font-weight:500">حساب نشط (يمكنه الدخول)</label>
              </div>
              <div style="display:flex; align-items:center; gap:6px">
                <input type="checkbox" name="hide_financials" id="user-hide-fin" value="1" style="width:auto">
                <label for="user-hide-fin" style="margin:0; font-size:13px; font-weight:500">إخفاء الأرباح والماليات</label>
              </div>
            </div>
          </div>
          <div class="btn-row" style="margin-top:20px">
            <button type="submit" class="btn" id="user-submit-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg> حفظ المستخدم</button>
            <button type="button" class="btn outline" style="display:none" id="user-cancel-btn" onclick="resetUserForm()">إلغاء التعديل</button>
          </div>
        </form>
      </div>
    </div>
  </div>
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
  function switchTab(tabId, el) {
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.getElementById('tab-' + tabId).style.display = 'block';
    
    document.querySelectorAll('.tabs .tab').forEach(el => el.classList.remove('active'));
    el.classList.add('active');
    
    localStorage.setItem('settings_active_tab', tabId);
  }

  // --- Expense Categories Edit Form Logic ---
  function editCat(id, name, desc) {
    document.getElementById('cat-form-title').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> <span>تعديل البند</span>';
    document.getElementById('cat-form').action = '/settings/expense-categories/' + id;
    document.getElementById('cat-method').value = 'PUT';
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-desc').value = desc;
    document.getElementById('cat-submit').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> <span>حفظ التعديلات</span>';
    document.getElementById('cat-submit').style.background = '#2563eb';
    document.getElementById('cat-cancel').style.display = 'flex';
    
    const card = document.getElementById('cat-form-card');
    card.style.borderColor = '#2563eb';
    card.style.background = '#eff6ff';
    
    // Scroll to form if needed
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function cancelCatEdit() {
    document.getElementById('cat-form-title').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> <span>إضافة بند مصروف جديد</span>';
    document.getElementById('cat-form').action = '/settings/expense-categories';
    document.getElementById('cat-method').value = 'POST';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-desc').value = '';
    document.getElementById('cat-submit').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg> <span>إضافة البند</span>';
    document.getElementById('cat-submit').style.background = '#3b82f6';
    document.getElementById('cat-cancel').style.display = 'none';
    
    const card = document.getElementById('cat-form-card');
    card.style.borderColor = '#e2e8f0';
    card.style.background = '#f8fafc';
  }

// Support switching tabs via URL hash (e.g. #accounts)
window.addEventListener('DOMContentLoaded', () => {
  const hash = window.location.hash.replace('#', '');
  if (hash && document.getElementById('tab-' + hash)) {
    const tabEl = Array.from(document.querySelectorAll('.tabs .tab')).find(t => t.getAttribute('onclick').includes(hash));
    if(tabEl) switchTab(hash, tabEl);
  }
});

function editUser(user) {
  document.getElementById('user-form-title').innerText = 'تعديل بيانات المستخدم: ' + user.name;
  const form = document.getElementById('user-form');
  form.action = '/settings/users/' + user.id;
  document.getElementById('user-method').value = 'PUT';
  
  document.getElementById('user-name').value = user.name;
  document.getElementById('user-username').value = user.username;
  document.getElementById('user-password').value = ''; // empty password input
  document.getElementById('user-role').value = user.role;
  document.getElementById('user-active').checked = user.is_active;
  document.getElementById('user-hide-fin').checked = user.hide_financials;
  
  document.getElementById('user-submit-btn').innerHTML = 'تحديث المستخدم';
  document.getElementById('user-cancel-btn').style.display = 'inline-flex';
  
  // switch to tab and scroll down
  switchTab('users', document.querySelector('.tabs .tab:nth-child(2)'));
  form.scrollIntoView({ behavior: 'smooth' });
}

function resetUserForm() {
  document.getElementById('user-form-title').innerText = 'إضافة مستخدم جديد';
  const form = document.getElementById('user-form');
  form.action = '{{ route("settings.users.store") }}';
  document.getElementById('user-method').value = 'POST';
  form.reset();
  
  document.getElementById('user-submit-btn').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg> حفظ المستخدم';
  document.getElementById('user-cancel-btn').style.display = 'none';
}
</script>
@endpush

@push('scripts')
<style>
  /* Override global accent color specifically for the settings page to make it more professional */
  :root {
    --accent: #3b82f6 !important;
    --accent-ink: #ffffff !important;
    --accent-soft: #eff6ff !important;
  }
  
  /* Additional UI polish */
  .tabs .tab.active {
    border-bottom: 3px solid #3b82f6;
    color: #1e293b;
    font-weight: 700;
  }
  .form-card {
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
  }
  .section-label {
    color: #3b82f6;
  }
</style>
@endpush
@endsection
