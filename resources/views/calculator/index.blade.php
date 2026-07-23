@extends('layouts.app')
@section('title', 'حاسبة المقايسة')
@section('page-title', 'حاسبة المقايسة')

@section('content')

<style>
  .calc-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: var(--bg-alt); padding: 4px; border-radius: 8px; width: max-content; }
  .calc-tab-btn { padding: 8px 24px; cursor: pointer; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s ease; }
  .calc-tab-btn:hover { color: var(--text); }
  .calc-tab-btn.active { background: white; color: var(--brand); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .dark .calc-tab-btn.active { background: var(--bg); }
  .calc-tab-content { display: none; animation: fadeIn 0.3s ease; }
  .calc-tab-content.active { display: block; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

  .space-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 10px;
    background: var(--bg);
    color: var(--text);
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
  }
  .space-input:hover {
    border-color: #cbd5e1;
  }
  .space-input:focus {
    border-color: #0ea5e9;
    outline: none;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    background: #f0f9ff;
  }
  .dark .space-input:focus {
    background: #082f49;
    border-color: #38bdf8;
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
  }
  
  table .space-input {
    padding: 8px 10px;
    font-size: 0.95rem;
    border-radius: 8px;
    border-width: 1px;
    box-shadow: none;
    font-weight: 500;
  }
  table .space-input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
  }

  .custom-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 700;
    color: #475569;
    font-size: 0.95rem;
  }
  .dark .custom-label { color: #cbd5e1; }
</style>

<div class="page-head">
  <div>
    <h3>حاسبة المقايسة</h3>
    <p>حساب مساحات الدهانات وسيراميك الأرضيات والحوائط بدقة وسرعة</p>
  </div>
</div>

<div class="calc-tabs">
  <button class="calc-tab-btn active" onclick="switchTab('calculator')">الحاسبة</button>
  <button class="calc-tab-btn" onclick="switchTab('history')">السجل</button>
</div>

<!-- تبويبة الحاسبة -->
<div id="tab-calculator" class="calc-tab-content active">
  <form method="POST" action="{{ route('calculator.store') }}" id="calcForm" onsubmit="prepareData()">
    @csrf

    @if ($errors->any())
      <div class="alert danger" style="margin-bottom: 20px;">
        <ul style="margin: 0; padding-right: 20px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    
    <div class="grid cols-2" style="margin-bottom: 24px; gap: 24px;">
      <div class="card" style="border-top: 4px solid #0ea5e9;">
        <label for="title" class="custom-label">عنوان الحسبة (اختياري)</label>
        <input type="text" name="title" id="title" class="space-input" placeholder="مثال: مقايسة فيلا العميل محمد...">
      </div>
      <div class="card grid cols-2" style="gap: 20px; border-top: 4px solid #38bdf8;">
        <div>
          <label for="workType" class="custom-label">نوع الحساب المطلوب</label>
          <select name="work_type" id="workType" class="space-input" onchange="calculateAll()">
            <option value="paints">دهانات (حوائط + سقف)</option>
            <option value="ceramics">سيراميك (أرضيات وحوائط)</option>
            <option value="both" selected>شامل (دهانات + سيراميك)</option>
          </select>
        </div>
        <div>
          <label for="globalHeight" class="custom-label">ارتفاع السقف (متر)</label>
          <input type="number" name="global_height" id="globalHeight" class="space-input" value="" placeholder="مثال: 2.8" step="0.1" min="1" oninput="calculateAll()">
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom: 24px">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><use href="#i-grid"/></svg>
          بيانات المساحات والغرف
        </h3>
        <button type="button" class="btn outline" onclick="addRow()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
          إضافة مساحة
        </button>
      </div>

      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th style="width: 18%;">اسم المساحة</th>
              <th style="width: 15%;">النوع</th>
              <th style="width: 12%;">الطول (م)</th>
              <th style="width: 12%;">العرض (م)</th>
              <th style="width: 12%;">الفتحات (م²)</th>
              <th>النتائج (م²)</th>
              <th style="width: 60px;">إجراء</th>
            </tr>
          </thead>
          <tbody id="spacesTable">
            <!-- الصفوف ستضاف هنا تلقائياً -->
          </tbody>
        </table>
      </div>
    </div>

    <h3 style="margin-bottom: 16px;">النتائج الإجمالية للمقايسة</h3>
    <div class="grid cols-4" style="margin-bottom: 24px">
      <div class="card stat">
        <div class="top"><span class="label">إجمالي الدهانات (حوائط + أسقف)</span></div>
        <div class="val tnum" style="color:var(--brand)"><span id="lblPaints">0.00</span> <small>م²</small></div>
      </div>
      <div class="card stat">
        <div class="top"><span class="label">سيراميك الأرضيات</span></div>
        <div class="val tnum" style="color:var(--amber)"><span id="lblFloorCeramics">0.00</span> <small>م²</small></div>
      </div>
      <div class="card stat">
        <div class="top"><span class="label">حوائط (حمامات ومطابخ)</span></div>
        <div class="val tnum" style="color:var(--teal)"><span id="lblWallCeramics">0.00</span> <small>م²</small></div>
      </div>
      <div class="card stat">
        <div class="top"><span class="label">إجمالي الخصومات</span></div>
        <div class="val tnum" style="color:var(--neg)"><span id="lblDeductions">0.00</span> <small>م²</small></div>
      </div>
    </div>

    <!-- Hidden fields to hold total results -->
    <input type="hidden" name="total_paints" id="totalPaintsInput" value="0">
    <input type="hidden" name="total_floor_ceramics" id="totalFloorCeramicsInput" value="0">
    <input type="hidden" name="total_wall_ceramics" id="totalWallCeramicsInput" value="0">
    <input type="hidden" name="total_deductions" id="totalDeductionsInput" value="0">
    <!-- JSON array for all rows -->
    <input type="hidden" name="spaces" id="spacesInput" value="[]">

    <div style="text-align: left; padding: 16px; background: var(--bg-alt); border-radius: 8px; border: 1px solid var(--border);">
      <button type="submit" class="btn primary lg">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="margin-inline-end: 8px;"><use href="#i-check"/></svg>
        حفظ الحسبة في السجل
      </button>
    </div>
  </form>
</div>

<!-- تبويبة السجل -->
<div id="tab-history" class="calc-tab-content">
  <div class="card">
    <h3 style="margin-top: 0;">سجل الحسابات السابقة</h3>
    @if($history->isEmpty())
      <div class="empty-state">لا يوجد سجلات حتى الآن</div>
    @else
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>العنوان</th>
              <th>النوع</th>
              <th>إجمالي الدهانات</th>
              <th>أرضيات سيراميك</th>
              <th>حوائط سيراميك</th>
              <th>تاريخ الحفظ</th>
              <th>إجراء</th>
            </tr>
          </thead>
          <tbody>
            @foreach($history as $item)
            <tr>
              <td><strong>{{ $item->title }}</strong></td>
              <td>
                @if($item->work_type == 'paints') دهانات @elseif($item->work_type == 'ceramics') سيراميك @else شامل @endif
              </td>
              <td>{{ $item->total_paints }} م²</td>
              <td>{{ $item->total_floor_ceramics }} م²</td>
              <td>{{ $item->total_wall_ceramics }} م²</td>
              <td class="muted">{{ $item->created_at->format('Y-m-d H:i') }}</td>
              <td>
                <form method="POST" action="{{ route('calculator.destroy', $item->id) }}" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn danger sm outline">حذف</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script>
  let rowCount = 0;

  document.addEventListener("DOMContentLoaded", function() {
    addRow('', 'room');
    addRow('', 'bath');
  });

  function switchTab(tabId) {
    document.querySelectorAll('.calc-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.calc-tab-content').forEach(content => content.classList.remove('active'));
    
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
  }

  function addRow(defaultName = '', defaultType = 'room') {
    rowCount++;
    const tbody = document.getElementById('spacesTable');
    const tr = document.createElement('tr');
    tr.id = `row-${rowCount}`;
    tr.classList.add('space-row');
    
    const name = defaultName || '';

    tr.innerHTML = `
      <td><input type="text" value="${name}" class="space-input inp-name"></td>
      <td>
        <select class="space-input inp-type" onchange="calculateAll();">
          <option value="room" ${defaultType === 'room' ? 'selected' : ''}>حجرة / صالة</option>
          <option value="bath" ${defaultType === 'bath' ? 'selected' : ''}>حمام / مطبخ</option>
        </select>
      </td>
      <td><input type="number" class="space-input inp-length" value="" step="0.1" min="0" oninput="calculateAll()"></td>
      <td><input type="number" class="space-input inp-width" value="" step="0.1" min="0" oninput="calculateAll()"></td>
      <td style="display: none;">
        <div class="balcony-options-container" id="balcony-opt-${rowCount}" style="display: none;">
        </div>
      </td>
      <td><input type="number" class="space-input inp-deduction" value="" step="0.1" min="0" oninput="calculateAll()" placeholder="خصم م²"></td>
      <td class="row-result" style="font-size: 0.9rem; color: #0ea5e9; font-weight:700">-</td>
      <td><button type="button" class="btn danger sm" onclick="removeRow('row-${rowCount}')">حذف</button></td>
    `;

    tbody.appendChild(tr);
    calculateAll();
  }

  function removeRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
      row.remove();
      calculateAll();
    }
  }

  function calculateAll() {
    const globalHeight = parseFloat(document.getElementById('globalHeight').value) || 0;
    const workType = document.getElementById('workType').value;
    const rows = document.querySelectorAll('#spacesTable tr.space-row');

    let totalPaints = 0;
    let totalFloorCeramics = 0;
    let totalWallCeramics = 0;
    let totalDeductions = 0;

    rows.forEach(row => {
      const type = row.querySelector('.inp-type').value;
      const length = parseFloat(row.querySelector('.inp-length').value) || 0;
      const width = parseFloat(row.querySelector('.inp-width').value) || 0;
      const deduction = parseFloat(row.querySelector('.inp-deduction').value) || 0;
      const resultCell = row.querySelector('.row-result');

      totalDeductions += deduction;

      let wallArea = 0;
      let floorArea = length * width;
      let rowText = '';

      const grossWalls = 2 * (length + width) * globalHeight;
      wallArea = Math.max(0, grossWalls - deduction);

      if (type === 'bath') {
        totalFloorCeramics += floorArea;
        totalWallCeramics += wallArea;
        rowText = `أرضية: ${floorArea.toFixed(1)}م² | حوائط: ${wallArea.toFixed(1)}م²`;
      } else {
        const ceilingArea = floorArea; // السقف = الطول × العرض
        if (workType === 'paints' || workType === 'both') {
          totalPaints += wallArea + ceilingArea;
        }
        if (workType === 'ceramics' || workType === 'both') {
          totalFloorCeramics += floorArea;
        }
        rowText = `حوائط: ${wallArea.toFixed(1)}م² | سقف: ${ceilingArea.toFixed(1)}م² | أرضية: ${floorArea.toFixed(1)}م²`;
      }

      resultCell.innerText = rowText;
    });

    document.getElementById('lblPaints').innerText = totalPaints.toFixed(2);
    document.getElementById('lblFloorCeramics').innerText = totalFloorCeramics.toFixed(2);
    document.getElementById('lblWallCeramics').innerText = totalWallCeramics.toFixed(2);
    document.getElementById('lblDeductions').innerText = totalDeductions.toFixed(2);

    document.getElementById('totalPaintsInput').value = totalPaints.toFixed(2);
    document.getElementById('totalFloorCeramicsInput').value = totalFloorCeramics.toFixed(2);
    document.getElementById('totalWallCeramicsInput').value = totalWallCeramics.toFixed(2);
    document.getElementById('totalDeductionsInput').value = totalDeductions.toFixed(2);
  }

  function prepareData() {
    const rows = document.querySelectorAll('#spacesTable tr.space-row');
    const spaces = [];
    rows.forEach(row => {
      spaces.push({
        name: row.querySelector('.inp-name').value,
        type: row.querySelector('.inp-type').value,
        length: row.querySelector('.inp-length').value,
        width: row.querySelector('.inp-width').value,
        deduction: row.querySelector('.inp-deduction').value
      });
    });
    document.getElementById('spacesInput').value = JSON.stringify(spaces);
  }
</script>
@endpush
