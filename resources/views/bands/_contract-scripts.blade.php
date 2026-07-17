{{-- Shared JS for the technician/worker list, used by bands/create.blade.php,
     bands/edit.blade.php and labor/create.blade.php. Every band's labor is
     always a list of one-or-more workers — each with their own contract type
     (بالمتر أو مقاولة مقطوعة), cost, client price, and supervision %. --}}
<script>
const CONTRACT_DESC = {
  lump_sum: 'مبلغ إجمالي ثابت مقابل تنفيذ الشغل بالكامل.',
  per_meter: 'بيتحدد بعدد الأمتار المنفذة × سعر المتر.',
  per_piece: 'بيتحدد بعدد القطع (أبواب/شبابيك...) × سعر القطعة.',
  daily: 'بيتحدد بعدد أيام الشغل × أجر اليوم.',
};

// Field labels differ between "بالمتر" (متر/سعر متر)، "بالقطعة" (قطع/سعر قطعة)
// and "يومية" (أيام/أجر يوم)
const QTY_LABELS = {
  per_meter: { qty: 'الكمية (متر)', cost: 'سعر المتر (تكلفة)', sell: 'سعر المتر للعميل' },
  per_piece: { qty: 'عدد القطع',    cost: 'سعر القطعة (تكلفة)', sell: 'سعر القطعة للعميل' },
  daily:     { qty: 'عدد الأيام',    cost: 'أجر اليوم (تكلفة)', sell: 'أجر اليوم للعميل' },
};

// Shows/hides the qty+rate fields based on the worker's contract type.
// per_meter, per_piece and daily all use the qty×rate fields (labels differ).
function updateWorkerUI(row) {
  if (! row) return;
  const type = row.querySelector('.contract-type-select').value;
  const descEl = row.querySelector('.contract-desc');
  const qtyWrap = row.querySelector('.contract-qty-wrap');

  if (descEl) descEl.textContent = CONTRACT_DESC[type] || '';

  if (type === 'per_meter' || type === 'per_piece' || type === 'daily') {
    qtyWrap.style.display = '';
    const lbl = QTY_LABELS[type];
    row.querySelector('.qty-label').textContent = lbl.qty;
    row.querySelector('.cost-rate-label').textContent = lbl.cost;
    row.querySelector('.sell-rate-label').textContent = lbl.sell;
    recalcWorkerAmounts(row);
  } else {
    qtyWrap.style.display = 'none';
    updateClientPrice();
  }
}

// per_meter/per_piece/daily: amount = qty × cost rate, sell_amount = qty ×
// sell rate — auto-computed as a convenient default, but NEVER locked: the
// user can always type a custom final amount directly (e.g. a negotiated
// adjustment after work started). Once they touch either field by hand, it
// stops following qty/rate changes so their override isn't silently wiped.
function recalcWorkerAmounts(row) {
  const qty = parseFloat(row.querySelector('.contract-qty').value) || 0;
  const rate = parseFloat(row.querySelector('.contract-rate').value) || 0;
  const sellRate = parseFloat(row.querySelector('.sell-rate').value) || 0;

  const amountField = row.querySelector('.final-amount');
  const sellField = row.querySelector('.sell-amount-field');

  if (amountField.dataset.touched !== '1') {
    amountField.value = (qty * rate).toFixed(2);
  }

  if (sellField.dataset.touched !== '1' && sellRate > 0) {
    sellField.value = (qty * sellRate).toFixed(2);
  }

  updateClientPrice();
}

// سعر العميل الإجمالي للبند = مجموع (سعر كل فني للعميل × (1 + نسبة إشرافه)) —
// بيتحدث تلقائيًا لحد ما المستخدم يعدّله بإيده (data-touched)
function updateClientPrice() {
  const clientField = document.getElementById('client-price-field');
  if (! clientField || clientField.dataset.touched === '1') return;

  let total = 0;
  document.querySelectorAll('.worker-row').forEach(row => {
    const amount = parseFloat(row.querySelector('.final-amount').value) || 0;
    const sellAmount = parseFloat(row.querySelector('.sell-amount-field').value) || 0;
    const pct = parseFloat(row.querySelector('.supervision-pct').value) || 0;
    const base = sellAmount || amount; // نفس منطق الفallback في السيرفر
    total += base * (1 + pct / 100);
  });

  clientField.value = total.toFixed(2);
}

const knownWorkersList = {!! $knownWorkersJson ?? '[]' !!};

function autocompleteWorker(inputEl) {
  const name = inputEl.value.trim();
  const worker = knownWorkersList.find(w => w.name === name);
  if (worker) {
    const row = inputEl.closest('.worker-row');
    const phoneInput = row.querySelector('input[name*="[phone]"]');
    const specInput = row.querySelector('input[name*="[specialty]"]');
    if (phoneInput && !phoneInput.value) phoneInput.value = worker.phone || '';
    if (specInput && !specInput.value) specInput.value = worker.specialty || '';
  }
}

function workerRowHtml(g) {
  return `
    <div class="worker-row" data-worker="${g}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:10px">
      {{-- Existing worker's id — keeps his دفعات attached when the band is re-saved --}}
      <input type="hidden" name="workers[${g}][id]" class="worker-id">
      <div class="row2">
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][name]" class="worker-name-input" placeholder="اسم الفني" required list="known-workers-list" oninput="autocompleteWorker(this)"></div>
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][phone]" placeholder="رقم الموبايل"></div>
      </div>
      <div class="row2" style="margin-top:10px">
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][specialty]" placeholder="التخصص (اختياري) مثل: كهربائي"></div>
        <div class="field" style="margin:0">
          <select name="workers[${g}][contract_type]" class="contract-type-select" onchange="updateWorkerUI(this.closest('.worker-row'))">
            <option value="">— نوع التعاقد —</option>
            <option value="lump_sum">مقاولة مقطوعة</option>
            <option value="per_meter">بالمتر</option>
            <option value="per_piece">بالقطعة</option>
            <option value="daily">يومية</option>
          </select>
          <p class="muted contract-desc" style="margin-top:6px"></p>
        </div>
      </div>
      <div class="row3 contract-qty-wrap" style="display:none;margin-top:10px">
        <div class="field" style="margin:0">
          <label class="qty-label" style="margin-bottom:4px">الكمية (متر)</label>
          <input type="number" name="workers[${g}][contract_qty]" class="contract-qty" min="0" step="0.01" oninput="recalcWorkerAmounts(this.closest('.worker-row'))">
        </div>
        <div class="field" style="margin:0">
          <label class="cost-rate-label" style="margin-bottom:4px">سعر المتر (تكلفة)</label>
          <input type="number" name="workers[${g}][contract_unit_rate]" class="contract-rate" min="0" step="0.01" oninput="recalcWorkerAmounts(this.closest('.worker-row'))">
        </div>
        <div class="field" style="margin:0">
          <label class="sell-rate-label" style="margin-bottom:4px">سعر المتر للعميل</label>
          <input type="number" name="workers[${g}][sell_rate]" class="sell-rate" min="0" step="0.01" oninput="recalcWorkerAmounts(this.closest('.worker-row'))">
        </div>
      </div>
      <div class="row3" style="margin-top:10px">
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">الأجر (تكلفة)</label>
          <input type="number" name="workers[${g}][amount]" class="final-amount" min="0" step="0.01" oninput="this.dataset.touched='1'; updateClientPrice()">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">سعره للعميل</label>
          <input type="number" name="workers[${g}][sell_amount]" class="sell-amount-field" min="0" step="0.01" oninput="this.dataset.touched='1'; updateClientPrice()">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">نسبة الإشراف %</label>
          <input type="number" name="workers[${g}][supervision_pct]" class="supervision-pct" min="0" max="100" step="0.1" value="{{ $defaultSupervisionPct ?? $settings->default_supervision_pct }}" oninput="updateClientPrice()">
        </div>
      </div>
      <div class="row2" style="margin-top:10px">
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">تاريخ بداية العمل معاه</label>
          <input type="date" name="workers[${g}][start_date]">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">ملاحظات</label>
          <input type="text" name="workers[${g}][notes]">
        </div>
      </div>
      <div class="btn-row worker-del-row" style="margin-top:8px">
        <button type="button" class="btn ghost sm danger worker-del-btn" onclick="this.closest('.worker-row').remove(); updateClientPrice(); if(typeof updateBandTabCounts==='function') updateBandTabCounts();">حذف الفني</button>
      </div>
    </div>`;
}

function fillWorker(g, w) {
  const row = document.querySelector(`.worker-row[data-worker="${g}"]`);
  row.querySelector('.worker-id').value = w.id || '';
  row.querySelector('[name*="[name]"]').value = w.name || '';
  row.querySelector('[name*="[phone]"]').value = w.phone || '';
  row.querySelector('[name*="[specialty]"]').value = w.specialty || '';
  row.querySelector('.contract-type-select').value = w.contract_type || '';
  row.querySelector('.contract-qty').value = w.contract_qty ?? '';
  row.querySelector('.contract-rate').value = w.contract_unit_rate ?? '';
  row.querySelector('.sell-rate').value = w.sell_rate ?? '';
  const amountField = row.querySelector('.final-amount');
  const sellField = row.querySelector('.sell-amount-field');
  amountField.value = w.amount ?? 0;
  sellField.value = w.sell_amount ?? 0;
  // بند/فني موجود بالفعل — القيمة المحفوظة تفضل زي ما هي بالظبط لما الفورم
  // يتفتح، مش تتحسب من جديد من الكمية×السعر فورًا (اللي ممكن تكون اتغيّرت
  // يدويًا قبل كده). ده قفل مؤقت لحد ما updateWorkerUI() تحته يخلص بس — لازم
  // نفكّه بعد كده على طول، وإلا أي تعديل لاحق في الكمية/السعر (تعديل مقايسة
  // عادي) هيفضل من غير أي تأثير على "الأجر" وهيبان للمستخدم إن حاجة متغيرتش.
  amountField.dataset.touched = '1';
  sellField.dataset.touched = '1';
  row.querySelector('.supervision-pct').value = w.supervision_pct ?? 0;
  row.querySelector('[name*="[start_date]"]').value = w.start_date || '';
  row.querySelector('[name*="[notes]"]').value = w.notes || '';
  updateWorkerUI(row);
  amountField.dataset.touched = '';
  sellField.dataset.touched = '';

  // فني اتدفعله جزء قبل كده؟ بياناته تفضل قابلة للتعديل زي أي فني تاني —
  // تعديل المقايسة (الكمية) بعد الدفع حاجة عادية جدًا وشائعة (زودت شغله من
  // 30 لـ 40 متر مثلاً)، فمفيش أي قفل. بس نوضّح إنه اتدفعله حاجة قبل كده
  // عشان المستخدم يبقى واعي وهو بيعدّل.
  if (w.has_payments) {
    const note = document.createElement('div');
    note.className = 'worker-paid-note';
    note.innerHTML = 'ℹ️ اتدفعله جزء من مستحقاته قبل كده — أي تعديل هنا (خصوصًا لو قلّلت الأجر عن المدفوع بالفعل) ممكن يأثر على حساب المتبقي له.';
    row.appendChild(note);
  }
}
</script>

<datalist id="known-workers-list">
  <script>
    document.write(knownWorkersList.map(w => `<option value="${w.name}">`).join(''));
  </script>
</datalist>
