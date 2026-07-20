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
              <button type="button" class="btn btn-dark btn-sm fw-bold px-3" onclick="toggleStmtForm({{ $c->id }},'settle')"><i class="fa fa-handshake me-1"></i>تسوية كاش (إنهاء مبكر)</button>
            @endif
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="toggleStmtForm({{ $c->id }},'edit')"><i class="fa fa-pen me-1"></i>تعديل</button>
            @php
                $txId = \App\Models\Transaction::where('ref_type', 'inst_down')->where('ref_id', $c->id)->value('id');
                $deleteUrl = $txId ? route('radar.index', ['tx' => $txId]) : route('radar.index');
            @endphp
            <a href="{{ $deleteUrl }}" class="btn btn-outline-danger btn-sm fw-bold px-3" target="_blank"><i class="fa fa-trash me-1"></i>حذف العقد (من الرادار)</a>
          </div>

          {{-- ── نموذج سداد (مخفي) ── --}}
          @if($instRemain > 0.009)
          <div id="stmtPay_{{ $c->id }}" class="no-print" style="display:none;position:fixed;inset:0;z-index:1060;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px);">
            <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 10px 25px rgba(0,0,0,0.2);overflow:hidden;display:flex;flex-direction:column;max-height:90vh;margin:auto;">
                <div style="background:#059669;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-weight:bold;font-size:18px;display:flex;align-items:center;gap:10px;">
                        <i class="fa fa-cash-register"></i> سداد قسط: {{ $cName }}
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="toggleStmtForm({{ $c->id }},'pay')" style="opacity:0.8;"></button>
                </div>
                <div style="padding:20px;overflow-y:auto;">
                    <form method="POST" action="{{ route('installments.pay', $c) }}" id="formPay_{{ $c->id }}">
                      @csrf
                      
                      <!-- المتبقي المطلوب -->
                      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:16px;text-align:center;margin-bottom:20px;">
                         <div style="color:#dc2626;font-weight:bold;font-size:18px;margin-bottom:4px;">المتبقي المطلوب:</div>
                         <div style="color:#dc2626;font-weight:900;font-size:28px;" dir="ltr"><span id="pay_remain_lbl_{{ $c->id }}">{{ \App\Support\Money::format($instRemain) }}</span> ج</div>
                      </div>

                      <!-- خصم / تسوية -->
                      <div style="border:1px solid #fbbf24;border-radius:8px;padding:16px;margin-bottom:20px;text-align:center;">
                         <label style="color:#d97706;font-weight:bold;display:block;margin-bottom:12px;">
                            <i class="fa fa-tag"></i> خصم / تسوية (يُطرح تلقائياً من المتبقي)
                         </label>
                         <input type="number" step="0.01" min="0" name="discount_applied" id="pay_disc_{{ $c->id }}" class="form-control text-center mx-auto" value="0" style="font-size:24px;font-weight:bold;color:#d97706;border-color:#fbbf24;padding:10px;max-width:300px;" oninput="stmtCalcRemain({{ $c->id }}, {{ $instRemain }}, {{ (float)$c->monthly_installment }})">
                      </div>

                      <!-- نظام السداد -->
                      <div style="margin-bottom:20px;">
                         <label style="font-weight:bold;display:block;margin-bottom:10px;text-align:center;">نظام السداد للمبلغ (بعد الخصم):</label>
                         <div style="display:flex;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;flex-wrap:wrap;">
                            <label style="flex:1;min-width:25%;text-align:center;padding:10px;cursor:pointer;border-inline-end:1px solid #e2e8f0;margin:0;" onclick="stmtSetPayStyle({{ $c->id }},'monthly', {{ (float)$c->monthly_installment }}, {{ $instRemain }})">
                               <input type="radio" name="pay_type_{{ $c->id }}" style="display:none;" value="monthly" checked>
                               <div id="ptl_monthly_{{ $c->id }}" style="font-weight:bold;color:#0369a1;background:#e0f2fe;padding:8px;border-radius:6px;font-size:13px;">قسط شهري ثابت</div>
                            </label>
                            <label style="flex:1;min-width:25%;text-align:center;padding:10px;cursor:pointer;border-inline-end:1px solid #e2e8f0;margin:0;" onclick="stmtSetPayStyle({{ $c->id }},'full', {{ (float)$c->monthly_installment }}, {{ $instRemain }})">
                               <input type="radio" name="pay_type_{{ $c->id }}" style="display:none;" value="full">
                               <div id="ptl_full_{{ $c->id }}" style="font-weight:bold;color:#475569;padding:8px;border-radius:6px;font-size:13px;">سداد كامل المتبقي</div>
                            </label>
                            <label style="flex:1;min-width:25%;text-align:center;padding:10px;cursor:pointer;border-inline-end:1px solid #e2e8f0;margin:0;" onclick="stmtSetPayStyle({{ $c->id }},'custom', {{ (float)$c->monthly_installment }}, {{ $instRemain }})">
                               <input type="radio" name="pay_type_{{ $c->id }}" style="display:none;" value="custom">
                               <div id="ptl_custom_{{ $c->id }}" style="font-weight:bold;color:#0284c7;padding:8px;border-radius:6px;font-size:13px;">مبلغ مخصص</div>
                            </label>
                            <label style="flex:1;min-width:25%;text-align:center;padding:10px;cursor:pointer;margin:0;" onclick="stmtSetPayStyle({{ $c->id }},'none', {{ (float)$c->monthly_installment }}, {{ $instRemain }})">
                               <input type="radio" name="pay_type_{{ $c->id }}" style="display:none;" value="none">
                               <div id="ptl_none_{{ $c->id }}" style="font-weight:bold;color:#dc2626;padding:8px;border-radius:6px;font-size:13px;"><i class="fa fa-triangle-exclamation"></i> تعثر (بدون دفع)</div>
                            </label>
                         </div>
                      </div>

                      <!-- المبلغ المطلوب -->
                      <div style="margin-bottom:20px;text-align:center;">
                         <label style="color:#059669;font-weight:bold;display:block;margin-bottom:12px;font-size:16px;">المبلغ المطلوب سداده كاش الآن <span style="color:red">*</span></label>
                         <input type="number" step="0.01" min="0" max="{{ $instRemain }}" name="amount_paid" id="pay_amt_{{ $c->id }}" class="form-control text-center mx-auto" required style="font-size:32px;font-weight:900;color:#059669;border:2px solid #059669;border-radius:8px;padding:12px;" value="{{ min((float)$c->monthly_installment, $instRemain) }}">
                      </div>

                      <div class="row g-3 mb-4">
                         <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:8px;font-size:14px;">إيداع في خزنة <span style="color:red">*</span></label>
                            <select name="account_id" id="pay_acc_{{ $c->id }}" class="form-select" required style="border-color:#3b82f6;color:#1d4ed8;font-weight:bold;">
                              <option value="" disabled {{ $c->account_id ? '' : 'selected' }}>— اختر الخزنة —</option>
                              @foreach(($wallets ?? collect()) as $w)
                                <option value="{{ $w->id }}" @selected($c->account_id == $w->id)>{{ $w->account_name }} — {{ \App\Support\Money::format($w->balance) }} ج</option>
                              @endforeach
                            </select>
                         </div>
                         <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:8px;font-size:14px;">تاريخ العملية / التعثر <span style="color:red">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required style="font-weight:bold;">
                         </div>
                      </div>
                      
                      <!-- مخفي -->
                      <input type="hidden" name="method" value="">
                      <input type="hidden" name="notes" id="pay_notes_{{ $c->id }}" value="">

                      <button type="submit" class="btn w-100" style="background:#059669;color:#fff;font-size:20px;font-weight:bold;padding:14px;border-radius:30px;"><i class="fa fa-check-circle me-2"></i> تأكيد التحصيل</button>
                    </form>
                </div>
            </div>
          </div>
          @endif

          {{-- ── نموذج تسوية كاش (مخفي) ── --}}
          @if($instRemain > 0.009)
          <div id="stmtSettle_{{ $c->id }}" class="no-print" style="display:none;position:fixed;inset:0;z-index:1060;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px);">
            <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 10px 25px rgba(0,0,0,0.2);overflow:hidden;display:flex;flex-direction:column;max-height:90vh;margin:auto;">
                <div style="background:#1e293b;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-weight:bold;font-size:18px;display:flex;align-items:center;gap:10px;">
                        <i class="fa fa-handshake"></i> تسوية كاش للعقد: {{ $cName }}
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="toggleStmtForm({{ $c->id }},'settle')" style="opacity:0.8;"></button>
                </div>
                <div style="padding:20px;overflow-y:auto;">
                    @php
                        $settleTotalPaidSoFar = (float)$c->down_payment + (float)$c->payments->sum('amount_paid') + (float)$c->payments->sum('discount_applied');
                        $settleCashDue = max(0, $afterDisc - $settleTotalPaidSoFar);
                        $settleDiscount = max(0, $instRemain - $settleCashDue);
                    @endphp
                    <form method="POST" action="{{ route('installments.settle', $c) }}" id="formSettle_{{ $c->id }}">
                      @csrf
                      
                      <!-- تفاصيل التسوية -->
                      <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:16px;margin-bottom:20px;">
                          <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                              <span style="color:#475569;font-weight:bold;">قيمة العقد كاش:</span>
                              <span style="font-weight:bold;" class="tnum">{{ \App\Support\Money::format($afterDisc) }} ج</span>
                          </div>
                          <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                              <span style="color:#475569;font-weight:bold;">إجمالي المدفوع حتى الآن:</span>
                              <span style="font-weight:bold;color:#16a34a;" class="tnum">{{ \App\Support\Money::format($settleTotalPaidSoFar) }} ج</span>
                          </div>
                          <hr style="margin:8px 0;border-color:#cbd5e1;">
                          <div style="display:flex;justify-content:space-between;">
                              <span style="color:#dc2626;font-weight:bold;">الفوائد التي سيتم إعفاؤها:</span>
                              <span style="font-weight:bold;color:#dc2626;" class="tnum">{{ \App\Support\Money::format($settleDiscount) }} ج</span>
                          </div>
                      </div>

                      <!-- المطلوب سداده كاش -->
                      <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:16px;text-align:center;margin-bottom:20px;">
                         <div style="color:#16a34a;font-weight:bold;font-size:18px;margin-bottom:4px;">المطلوب سداده الآن كاش:</div>
                         <div style="color:#16a34a;font-weight:900;font-size:28px;" class="tnum" dir="ltr">{{ \App\Support\Money::format($settleCashDue) }} <small>ج</small></div>
                      </div>

                      <div class="row g-2 mb-3">
                         <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:6px;">الخزينة المحصل عليها <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select form-select-lg" required style="border-color:#3b82f6;color:#1d4ed8;font-weight:bold;">
                               <option value="" disabled {{ $c->account_id ? '' : 'selected' }}>— اختر الخزنة —</option>
                               @foreach(($wallets ?? collect()) as $w)
                                  <option value="{{ $w->id }}" @selected($c->account_id == $w->id)>{{ $w->account_name }} — {{ \App\Support\Money::format($w->balance) }} ج</option>
                               @endforeach
                            </select>
                         </div>
                         <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:6px;">تاريخ التسوية <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-lg text-center fw-bold" value="{{ date('Y-m-d') }}" required>
                         </div>
                      </div>

                      <div style="margin-bottom:20px;">
                         <label style="font-weight:bold;display:block;margin-bottom:6px;">ملاحظات (اختياري)</label>
                         <input type="text" name="notes" class="form-control" placeholder="مثال: تم سداد باقي الكاش وتسوية العقد نهائياً">
                      </div>

                      <button type="submit" class="btn w-100" style="background:#1e293b;color:#fff;font-size:18px;font-weight:bold;padding:12px;border-radius:30px;"><i class="fa fa-handshake me-2"></i> تأكيد التسوية وإنهاء العقد</button>
                    </form>
                </div>
            </div>
          </div>
          @endif

          {{-- ── نموذج تعديل (مخفي) ── --}}
          <div id="stmtEdit_{{ $c->id }}" class="no-print" style="display:none;background:#eef2ff;border-bottom:1px solid #c7d2fe;padding:12px;">
            <form method="POST" action="{{ route('installments.update', $c) }}">
              @csrf @method('PUT')
              <div class="row g-2">
                <div class="col-4"><label class="small fw-bold">اسم العميل</label><input name="customer_name" class="form-control form-control-sm" value="{{ $c->customer_name }}" required></div>
                <div class="col-4"><label class="small fw-bold">الموبايل</label><input name="customer_phone" class="form-control form-control-sm" value="{{ $c->customer_phone }}"></div>
                <div class="col-4"><label class="small fw-bold">يوم السداد</label><input type="number" step="1" min="1" max="31" name="due_day" class="form-control form-control-sm" value="{{ (int)$c->due_day }}" required></div>
              </div>
              <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary btn-sm fw-bold"><i class="fa fa-save me-1"></i>حفظ التعديل</button>
                <button type="button" class="btn btn-light btn-sm fw-bold" onclick="toggleStmtForm({{ $c->id }},'edit')">إلغاء</button>
              </div>
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
                <span class="pxls-row-badge">{{ $pIdx + 1 }}</span>
                <span class="pxls-pay-amount">{{ fmtMoney($p->amount_paid) }}@if((float)$p->discount_applied > 0)<small style="color:#0277bd;"> (+خصم {{ fmtMoney($p->discount_applied) }})</small>@endif</span>
                <button type="button" class="no-print" onclick="deleteInstallmentPayment({{ $p->id }}, '{{ $groupKey }}')" style="position:absolute;left:35px;top:50%;transform:translateY(-50%);background:none;border:none;color:#ef4444;opacity:0.4;font-size:12px;cursor:pointer;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.4'" title="حذف الدفعة"><i class="fa fa-trash"></i></button>
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
