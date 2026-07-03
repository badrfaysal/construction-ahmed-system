{{-- Shared JS for the technician/worker list, used by bands/create.blade.php,
     bands/edit.blade.php and labor/create.blade.php. Every band's labor is
     always a list of one-or-more workers — each with their own contract type
     (بالمتر أو مقاولة مقطوعة), cost, client price, and supervision %. --}}
<script>
const CONTRACT_DESC = {
  lump_sum: 'مبلغ إجمالي ثابت مقابل تنفيذ الشغل بالكامل.',
  per_meter: 'بيتحدد بعدد الأمتار المنفذة × سعر المتر.',
  daily: 'بيتحدد بعدد أيام الشغل × أجر اليوم.',
};

// Field labels differ between "بالمتر" (متر/سعر متر) and "يومية" (أيام/أجر يوم)
const QTY_LABELS = {
  per_meter: { qty: 'الكمية (متر)', cost: 'سعر المتر (تكلفة)', sell: 'سعر المتر للعميل' },
  daily:     { qty: 'عدد الأيام',    cost: 'أجر اليوم (تكلفة)', sell: 'أجر اليوم للعميل' },
};

// Shows/hides the qty+rate fields based on the worker's contract type.
// Both per_meter and daily use the qty×rate fields (only the labels differ).
function updateWorkerUI(row) {
  if (! row) return;
  const type = row.querySelector('.contract-type-select').value;
  const descEl = row.querySelector('.contract-desc');
  const qtyWrap = row.querySelector('.contract-qty-wrap');

  if (descEl) descEl.textContent = CONTRACT_DESC[type] || '';

  if (type === 'per_meter' || type === 'daily') {
    qtyWrap.style.display = '';
    const lbl = QTY_LABELS[type];
    row.querySelector('.qty-label').textContent = lbl.qty;
    row.querySelector('.cost-rate-label').textContent = lbl.cost;
    row.querySelector('.sell-rate-label').textContent = lbl.sell;
    recalcWorkerAmounts(row);
  } else {
    qtyWrap.style.display = 'none';
    row.querySelector('.final-amount').readOnly = false;
    row.querySelector('.sell-amount-field').readOnly = false;
    updateClientPrice();
  }
}

// per_meter: amount = qty × cost rate, sell_amount = qty × sell rate (لو متملي)
function recalcWorkerAmounts(row) {
  const qty = parseFloat(row.querySelector('.contract-qty').value) || 0;
  const rate = parseFloat(row.querySelector('.contract-rate').value) || 0;
  const sellRate = parseFloat(row.querySelector('.sell-rate').value) || 0;

  const amountField = row.querySelector('.final-amount');
  const sellField = row.querySelector('.sell-amount-field');

  amountField.value = (qty * rate).toFixed(2);
  amountField.readOnly = true;

  if (sellRate > 0) {
    sellField.value = (qty * sellRate).toFixed(2);
    sellField.readOnly = true;
  } else {
    sellField.readOnly = false;
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

function workerRowHtml(g) {
  return `
    <div class="worker-row" data-worker="${g}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:10px">
      <div class="row2">
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][name]" placeholder="اسم الفني" required></div>
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][phone]" placeholder="رقم الموبايل"></div>
      </div>
      <div class="row2" style="margin-top:10px">
        <div class="field" style="margin:0"><input type="text" name="workers[${g}][specialty]" placeholder="التخصص (اختياري) مثل: كهربائي"></div>
        <div class="field" style="margin:0">
          <select name="workers[${g}][contract_type]" class="contract-type-select" onchange="updateWorkerUI(this.closest('.worker-row'))">
            <option value="">— نوع التعاقد —</option>
            <option value="lump_sum">مقاولة مقطوعة</option>
            <option value="per_meter">بالمتر</option>
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
          <input type="number" name="workers[${g}][amount]" class="final-amount" min="0" step="0.01" oninput="updateClientPrice()">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">سعره للعميل</label>
          <input type="number" name="workers[${g}][sell_amount]" class="sell-amount-field" min="0" step="0.01" oninput="updateClientPrice()">
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
      <div class="btn-row" style="margin-top:8px">
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.worker-row').remove(); updateClientPrice()">حذف الفني</button>
      </div>
    </div>`;
}

function fillWorker(g, w) {
  const row = document.querySelector(`.worker-row[data-worker="${g}"]`);
  row.querySelector('[name*="[name]"]').value = w.name || '';
  row.querySelector('[name*="[phone]"]').value = w.phone || '';
  row.querySelector('[name*="[specialty]"]').value = w.specialty || '';
  row.querySelector('.contract-type-select').value = w.contract_type || '';
  row.querySelector('.contract-qty').value = w.contract_qty ?? '';
  row.querySelector('.contract-rate').value = w.contract_unit_rate ?? '';
  row.querySelector('.sell-rate').value = w.sell_rate ?? '';
  row.querySelector('.final-amount').value = w.amount ?? 0;
  row.querySelector('.sell-amount-field').value = w.sell_amount ?? 0;
  row.querySelector('.supervision-pct').value = w.supervision_pct ?? 0;
  row.querySelector('[name*="[start_date]"]').value = w.start_date || '';
  row.querySelector('[name*="[notes]"]').value = w.notes || '';
  updateWorkerUI(row);
}
</script>
