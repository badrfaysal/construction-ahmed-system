@php
    // مطابق لكشف حساب العقد في السيستم الأول — بس بداتا العقود المربوطة بالمشاريع.
    if (!function_exists('fmtMoney')) {
        function fmtMoney($val) {
            return \App\Support\Money::format($val);
        }
    }
    $cName        = $customerName;
    $cPhone       = $customerPhone ?: '—';
    $activeConts  = $contracts->filter(fn($c) => (float)$c->remaining_balance > 0.009);
    $doneConts    = $contracts->filter(fn($c) => (float)$c->remaining_balance <= 0.009);
    $countAll     = $contracts->count();
    $totalContAll = $contracts->sum(fn($c) => (float)$c->total_after_interest);
    $totalDownAll = $contracts->sum(fn($c) => (float)$c->down_payment);
    $totalPaidAll = $contracts->sum(fn($c) => (float)$c->payments->sum('amount_paid'));
    $totalMonthly = $activeConts->sum(fn($c) => (float)$c->monthly_installment);
    $totalRemAll  = $contracts->sum(fn($c) => max(0,(float)$c->remaining_balance));
    $groupKey     = 'grp_' . md5(($cPhone !== '—' ? $cPhone : 'n:'.$cName));

    // واتساب: نص كل عقد + نص كل العقود
    $waPhone = preg_replace('/\D/', '', (string) $customerPhone);
    if ($waPhone && str_starts_with($waPhone, '0')) { $waPhone = '2' . $waPhone; }
    $waAllLines = ['*كشف حساب — ' . $cName . '*',
        'عدد العقود: ' . $countAll,
        'إجمالي قيمة العقود: ' . fmtMoney($totalContAll) . ' ج',
        'إجمالي المسدد: ' . fmtMoney($totalDownAll + $totalPaidAll) . ' ج',
        'إجمالي المتبقي: ' . fmtMoney($totalRemAll) . ' ج'];
    $waAllUrl = $waPhone ? ('https://wa.me/' . $waPhone . '?text=' . urlencode(implode("\n", $waAllLines))) : '';
@endphp

<div class="modal fade" id="customerModal_{{ $groupKey }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
    <div class="modal-content border-0" style="border-radius:8px;overflow:hidden;">

      {{-- ── Header ── --}}
      <div style="background:#0f172a;color:#fff;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
          <div style="width:38px;height:38px;border-radius:9px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;">
            <i class="fa fa-file-lines" style="font-size:16px;color:#fff;"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:600;">كشف حساب — {{ $cName }}</div>
            <div style="font-size:11px;opacity:.65;margin-top:2px;" dir="ltr">{{ $cPhone }}</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button onclick="printCustomerStatement('{{ $groupKey }}')" class="btn btn-sm" style="background:#4f46e5;color:#fff;font-size:12px;padding:6px 14px;border-radius:8px;border:none;font-weight:500;"><i class="fa fa-print me-1"></i>طباعة</button>
          <button onclick="downloadCustomerSheet('{{ $groupKey }}')" class="btn btn-sm" style="background:rgba(255,255,255,.12);color:#fff;font-size:12px;padding:6px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.18);font-weight:500;"><i class="fa fa-download me-1"></i>تحميل</button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>

      {{-- ── Tabs strip ── --}}
      <div id="tabs_{{ $groupKey }}" class="cst-tabs-strip">
        @if($countAll > 1)
        <div class="cst-tab cst-tab-summary active-tab" data-pane="summary" onclick="switchTab('{{ $groupKey }}','summary')">
          <i class="fa fa-chart-pie" style="font-size:10px;"></i> ملخص <span class="cst-num">{{ $countAll }}</span>
        </div>
        @endif
        @foreach($contracts as $tIdx => $tc)
        <div class="cst-tab {{ $countAll == 1 ? 'active-tab' : '' }}" data-pane="contract_{{ $tc->id }}" onclick="switchTab('{{ $groupKey }}','contract_{{ $tc->id }}')">
          @if((float)$tc->remaining_balance > 0.009)
            <span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;flex-shrink:0;"></span>
          @else
            <i class="fa fa-check" style="color:#059669;font-size:9px;"></i>
          @endif
          {{ Str::limit($tc->product_name, 14) }}
          <span class="cst-num">{{ $tIdx + 1 }}</span>
        </div>
        @endforeach
      </div>

      {{-- ── Body ── --}}
      <div class="modal-body p-0" style="background:#fff;" id="captureCustomer_{{ $groupKey }}"
           data-active-pane="{{ $countAll == 1 ? 'contract_'.$contracts->first()->id : 'summary' }}"
           data-customer-name="{{ $cName }}" data-wa-all="{{ $waAllUrl }}">

        <div style="background:#0f172a;color:#fff;padding:12px 18px;text-align:center;display:none;" class="print-header-{{ $groupKey }}">
          <div style="font-size:15px;font-weight:600;">{{ $settings->company_name ?? 'كشف حساب' }} — كشف حساب تقسيط</div>
          <div style="font-size:12px;opacity:.7;margin-top:3px;">العميل: {{ $cName }} | تاريخ الطباعة: {{ date('Y-m-d') }}</div>
        </div>

        {{-- ملخص (لو أكتر من عقد) --}}
        @if($countAll > 1)
        <div id="pane_{{ $groupKey }}_summary" class="cst-pane" style="display:block;">
          <table class="paper-xls">
            <tr class="pxls-title-row"><td class="pxls-label">اسم العميل</td><td class="pxls-value name-val">{{ $cName }}</td></tr>
            <tr><td class="pxls-label">رقم الموبايل</td><td class="pxls-value" dir="ltr">{{ $cPhone }}</td></tr>
            <tr><td class="pxls-label">إجمالي العقود</td><td class="pxls-value">{{ $countAll }} عقد ({{ $activeConts->count() }} نشط — {{ $doneConts->count() }} مكتمل)</td></tr>
            <tr><td class="pxls-label">إجمالي قيمة العقود</td><td class="pxls-value">{{ fmtMoney($totalContAll) }}</td></tr>
            <tr><td class="pxls-label">إجمالي المقدمات</td><td class="pxls-value">{{ fmtMoney($totalDownAll) }}</td></tr>
            <tr><td class="pxls-label">إجمالي المسدد</td><td class="pxls-value paid-val">{{ fmtMoney($totalDownAll + $totalPaidAll) }}</td></tr>
            <tr><td class="pxls-label">إجمالي الأقساط الشهرية</td><td class="pxls-value">{{ fmtMoney($totalMonthly) }}</td></tr>
            <tr><td class="pxls-label remaining-label">إجمالي المتبقي بالخارج</td><td class="pxls-value remaining-val">{{ fmtMoney($totalRemAll) }}</td></tr>
          </table>
        </div>
        @endif

        {{-- كل عقد على حدة --}}
        @foreach($contracts as $c)
        @php
            $afterDisc  = max(0, (float)$c->cash_price - (float)$c->discount);
            $instPaid   = (float)$c->payments->sum('amount_paid');
            $instRemain = (float)$c->remaining_balance;
            $paysSorted = $c->payments->sortBy('payment_date')->values();
            // نص واتساب لهذا العقد تحديدًا
            $waLines = ['*كشف حساب عقد — ' . $cName . '*',
                'المتعاقد عليه: ' . $c->product_name,
                'إجمالي العقد: ' . fmtMoney($c->total_after_interest) . ' ج',
                'المقدم: ' . fmtMoney($c->down_payment) . ' ج',
                'القسط الشهري: ' . fmtMoney($c->monthly_installment) . ' ج (يوم ' . $c->due_day . ')',
                'المسدد: ' . fmtMoney($c->down_payment + $instPaid) . ' ج',
                'المتبقي: ' . fmtMoney($instRemain) . ' ج'];
            $waUrl = $waPhone ? ('https://wa.me/' . $waPhone . '?text=' . urlencode(implode("\n", $waLines))) : '';
        @endphp
        <div id="pane_{{ $groupKey }}_contract_{{ $c->id }}" class="cst-pane" data-product="{{ $c->product_name }}" data-wa="{{ $waUrl }}"
             style="display:{{ $countAll == 1 ? 'block' : 'none' }};">

          {{-- شريط الإجراءات --}}
          <div class="sheet-no-export no-print" style="background:#fff8e1;border-bottom:1px solid #ffe082;padding:8px 12px;display:flex;gap:8px;flex-wrap:wrap;justify-content:center;">
            @if($instRemain > 0.009)
              <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="toggleStmtForm({{ $c->id }},'pay')"><i class="fa fa-cash-register me-1"></i>سداد قسط</button>
            @endif
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="toggleStmtForm({{ $c->id }},'edit')"><i class="fa fa-pen me-1"></i>تعديل</button>
            <span class="small text-muted align-self-center">حذف العقد من <a href="{{ route('transactions.index') }}">سجل الحركات</a></span>
          </div>

          {{-- ── نموذج سداد (مخفي) ── --}}
          @if($instRemain > 0.009)
          <div id="stmtPay_{{ $c->id }}" class="no-print" style="display:none;background:#f0fdf4;border-bottom:1px solid #bbf7d0;padding:12px;">
            <form method="POST" action="{{ route('installments.pay', $c) }}">
              @csrf
              <div class="d-flex gap-1 mb-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="stmtSetPay({{ $c->id }},{{ (float)$c->monthly_installment }},{{ $instRemain }},'monthly')">قسط شهري</button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="stmtSetPay({{ $c->id }},{{ (float)$c->monthly_installment }},{{ $instRemain }},'full')">سداد كامل</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="stmtSetPay({{ $c->id }},{{ (float)$c->monthly_installment }},{{ $instRemain }},'partial')">مبلغ مخصص</button>
              </div>
              <div class="d-flex align-items-end gap-2 flex-wrap">
                <div><label class="small fw-bold d-block">المبلغ</label><input type="number" step="0.01" min="0" max="{{ $instRemain }}" name="amount_paid" id="pay_amt_{{ $c->id }}" class="form-control form-control-sm fw-bold" required></div>
                <div><label class="small fw-bold d-block">خصم</label><input type="number" step="0.01" min="0" name="discount_applied" class="form-control form-control-sm" value="0" style="max-width:90px"></div>
                <div><label class="small fw-bold d-block">تاريخ</label><input type="date" name="payment_date" class="form-control form-control-sm" value="{{ today()->format('Y-m-d') }}" required></div>
                <div><label class="small fw-bold d-block">الطريقة</label><input type="text" name="method" class="form-control form-control-sm" placeholder="كاش/تحويل" style="max-width:110px"></div>
                <div style="min-width:150px"><label class="small fw-bold d-block"><i class="fa fa-wallet text-primary"></i> المحفظة *</label>
                  <select name="account_id" class="form-select form-select-sm" required>
                    <option value="" disabled {{ $c->account_id ? '' : 'selected' }}>— اختر المحفظة —</option>
                    @foreach(($wallets ?? collect()) as $w)
                      <option value="{{ $w->id }}" @selected($c->account_id == $w->id)>{{ $w->account_name }} — {{ \App\Support\Money::format($w->balance) }} ج</option>
                    @endforeach
                  </select></div>
                <div style="flex:1;min-width:110px"><label class="small fw-bold d-block">ملاحظات</label><input type="text" name="notes" class="form-control form-control-sm" placeholder="اختياري"></div>
                <button class="btn btn-success btn-sm fw-bold"><i class="fa fa-check me-1"></i>تحصيل</button>
              </div>
            </form>
          </div>
          @endif

          {{-- ── نموذج تعديل (مخفي) ── --}}
          <div id="stmtEdit_{{ $c->id }}" class="no-print" style="display:none;background:#eef2ff;border-bottom:1px solid #c7d2fe;padding:12px;">
            <form method="POST" action="{{ route('installments.update', $c) }}">
              @csrf @method('PUT')
              <div class="row g-2">
                <div class="col-6"><label class="small fw-bold">اسم العميل</label><input name="customer_name" class="form-control form-control-sm" value="{{ $c->customer_name }}" required></div>
                <div class="col-6"><label class="small fw-bold">الموبايل</label><input name="customer_phone" class="form-control form-control-sm" value="{{ $c->customer_phone }}"></div>
                <div class="col-12"><label class="small fw-bold">المتعاقد عليه</label><input name="product_name" class="form-control form-control-sm" value="{{ $c->product_name }}"></div>
                <div class="col-4"><label class="small fw-bold">قيمة العقد (كاش)</label><input type="number" step="0.01" min="0" name="cash_price" class="form-control form-control-sm" value="{{ (float)$c->cash_price }}" required></div>
                <div class="col-4"><label class="small fw-bold">خصم</label><input type="number" step="0.01" min="0" name="discount" class="form-control form-control-sm" value="{{ (float)$c->discount }}"></div>
                <div class="col-4"><label class="small fw-bold">المقدم</label><input type="number" step="0.01" min="0" name="down_payment" class="form-control form-control-sm" value="{{ (float)$c->down_payment }}"></div>
                <div class="col-4"><label class="small fw-bold">نسبة الفائدة %</label><input type="number" step="0.1" min="0" name="interest_rate" class="form-control form-control-sm" value="{{ (float)$c->interest_rate }}"></div>
                <div class="col-4"><label class="small fw-bold">عدد الشهور</label><input type="number" step="1" min="1" name="installment_months" class="form-control form-control-sm" value="{{ (int)$c->installment_months }}" required></div>
                <div class="col-4"><label class="small fw-bold">يوم السداد</label><input type="number" step="1" min="1" max="31" name="due_day" class="form-control form-control-sm" value="{{ (int)$c->due_day }}" required></div>
                <div class="col-6"><label class="small fw-bold">تاريخ العقد</label><input type="date" name="start_date" class="form-control form-control-sm" value="{{ optional($c->start_date)->format('Y-m-d') ?? $c->created_at->format('Y-m-d') }}" required></div>
                <div class="col-6"><label class="small fw-bold">ملاحظات</label><input name="notes" class="form-control form-control-sm" value="{{ $c->notes }}"></div>
              </div>
              <div class="d-flex gap-2 mt-2">
                <button class="btn btn-primary btn-sm fw-bold"><i class="fa fa-save me-1"></i>حفظ التعديل</button>
                <button type="button" class="btn btn-light btn-sm fw-bold" onclick="toggleStmtForm({{ $c->id }},'edit')">إلغاء</button>
              </div>
              <div class="small text-muted mt-1">ملاحظة: تعديل المقدم بيعدّل حركته في المحفظة تلقائيًا، والمتبقي بيتعاد حسابه مع مراعاة الدفعات المسجّلة.</div>
            </form>
          </div>

          {{-- ── جدول كشف الحساب (paper-xls) ── --}}
          <table class="paper-xls">
            <tr class="pxls-title-row"><td class="pxls-label">اسم العميل</td><td class="pxls-value name-val">{{ $c->customer_name }}</td></tr>
            <tr><td class="pxls-label">المتعاقد عليه</td><td class="pxls-value">{{ $c->product_name }}</td></tr>
            <tr><td class="pxls-label">تاريخ التعاقد</td><td class="pxls-value" dir="ltr">{{ \Carbon\Carbon::parse($c->start_date ?? $c->created_at)->format('Y-m-d') }}</td></tr>
            <tr><td class="pxls-label">قيمة العقد كاش</td><td class="pxls-value">{{ fmtMoney($c->cash_price) }}</td></tr>
            @if((float)$c->discount > 0)
            <tr><td class="pxls-label" style="color:#0277bd;">خصم</td><td class="pxls-value" style="color:#0277bd;">{{ fmtMoney($c->discount) }}</td></tr>
            @endif
            <tr><td class="pxls-label">المقدم</td><td class="pxls-value">{{ fmtMoney($c->down_payment) }}</td></tr>
            <tr><td class="pxls-label">متبقي بعد دفع المقدم (قبل الفوائد)</td><td class="pxls-value">{{ fmtMoney(max(0, $afterDisc - $c->down_payment)) }}</td></tr>
            <tr><td class="pxls-label">عدد الأشهر</td><td class="pxls-value">{{ $c->installment_months }}</td></tr>
            @if((float)$c->interest_rate > 0)
            <tr><td class="pxls-label">نسبة مئوية</td><td class="pxls-value">{{ rtrim(rtrim(number_format($c->interest_rate,2),'0'),'.') }}%</td></tr>
            @endif
            <tr><td class="pxls-label">إجمالي المتبقي بعد النسبة</td><td class="pxls-value">{{ fmtMoney($c->total_after_interest - $c->down_payment) }}</td></tr>
            <tr><td class="pxls-label">القسط الشهري</td><td class="pxls-value">{{ fmtMoney($c->monthly_installment) }}</td></tr>
            <tr><td class="pxls-label">موعد سداد القسط</td><td class="pxls-value">{{ $c->due_day }}</td></tr>
            <tr><td class="pxls-label">رقم الموبايل</td><td class="pxls-value" dir="ltr">{{ $c->customer_phone ?: '—' }}</td></tr>

            @forelse($paysSorted as $pIdx => $p)
            <tr class="pxls-pay-row">
              <td class="pxls-pay-date" dir="ltr">{{ optional($p->payment_date)->format('Y-m-d') }}</td>
              <td class="pxls-pay-num" style="position:relative;">
                <span class="pxls-pay-amount">{{ fmtMoney($p->amount_paid) }}@if((float)$p->discount_applied > 0)<small style="color:#0277bd;"> (+خصم {{ fmtMoney($p->discount_applied) }})</small>@endif</span>
                <span class="pxls-row-badge">{{ $pIdx + 1 }}</span>
              </td>
            </tr>
            @empty
            <tr class="pxls-pay-row"><td colspan="2" style="text-align:center;color:#9e9e9e;padding:10px;font-size:13px;">لم يتم سداد أي دفعات حتى الآن</td></tr>
            @endforelse

            @for($emptyRow = $paysSorted->count() + 1; $emptyRow <= (int)$c->installment_months; $emptyRow++)
            <tr class="pxls-empty-row"><td class="pxls-pay-date" style="color:#bdbdbd;">—</td><td class="pxls-pay-num"><span class="pxls-row-badge" style="background:#e0e0e0;color:#9e9e9e;">{{ $emptyRow }}</span></td></tr>
            @endfor

            <tr class="pxls-summary-row paid-summary"><td class="pxls-sum-label">إجمالي المدفوع</td><td class="pxls-sum-value paid-val">{{ fmtMoney($instPaid) }}</td></tr>
            <tr class="pxls-summary-row remaining-summary"><td class="pxls-sum-label remaining-label">إجمالي المتبقي</td><td class="pxls-sum-value remaining-val">{{ fmtMoney($instRemain) }}</td></tr>
          </table>
        </div>
        @endforeach
      </div>

      {{-- ── Footer ── --}}
      <div style="background:#fafbfd;border-top:1px solid #e6ebf3;padding:12px 18px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <button onclick="printCustomerStatement('{{ $groupKey }}')" class="btn" style="background:#4f46e5;color:#fff;font-size:13px;padding:9px 22px;border-radius:8px;border:none;font-weight:500;"><i class="fa fa-print me-2"></i> طباعة الكشف</button>
        <button onclick="downloadCustomerSheet('{{ $groupKey }}')" class="btn" style="background:#fff;color:#5a6478;font-size:13px;padding:9px 22px;border-radius:8px;border:1px solid #e6ebf3;font-weight:500;"><i class="fa fa-download me-2"></i> تحميل كصورة</button>
        @if($waPhone)
        <button onclick="sendCustomerSheetWhatsApp('{{ $groupKey }}')" class="btn" style="background:#25d366;color:#fff;font-size:13px;padding:9px 22px;border-radius:8px;border:none;font-weight:600;"><i class="fab fa-whatsapp me-2"></i> إرسال العقد الحالي</button>
        @if($countAll > 1)
        <button onclick="sendAllContractsWhatsApp('{{ $groupKey }}')" class="btn" style="background:#128c7e;color:#fff;font-size:13px;padding:9px 22px;border-radius:8px;border:none;font-weight:600;"><i class="fab fa-whatsapp me-2"></i> إرسال كل العقود ({{ $countAll }})</button>
        @endif
        @endif
      </div>

    </div>
  </div>
</div>
