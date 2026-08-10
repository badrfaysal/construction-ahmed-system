@php
  $assetData = [
     'id' => $asset->id,
     'name' => $asset->name,
     'status' => $asset->status == 'active' ? 'نشط' : ($asset->status == 'sold' ? 'مُباع' : 'مهلك بالكامل'),
     'purchase_cost' => \App\Support\Money::format($asset->purchase_cost),
     'current_value' => \App\Support\Money::format($asset->current_value),
     'depreciated' => \App\Support\Money::format($asset->purchase_cost - $asset->current_value),
     'date' => \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d'),
     'account' => $asset->account ? $asset->account->name : 'بدون حساب مالي',
     'notes' => $asset->notes ?: '—',
     'logs' => $asset->logs->map(function($l) {
         return [
             'date' => $l->created_at->format('Y-m-d H:i'),
             'action' => $l->action,
             'notes' => $l->notes,
         ];
     })->toArray()
  ];
@endphp
<tr class="main-row" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" onclick="if(!event.target.closest('button') && !event.target.closest('a')) openAssetDetails(this)" data-asset='{{ json_encode($assetData, JSON_HEX_APOS) }}'>
  <td class="muted">{{ $index }}</td>
  <td>
    <div style="font-weight: 600; display: flex; align-items: center; gap: 6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      {{ $asset->name }}
    </div>
    <div style="font-size: 11px; margin-top: 4px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      @if($asset->account) سحب من: {{ $asset->account->name }} @else إهلاك يدوي فقط @endif
    </div>
  </td>
  <td class="num" style="color: var(--brand)">{{ \App\Support\Money::format($asset->purchase_cost) }} <small>ج.م</small></td>
  <td class="num" style="color: {{ $asset->status == 'active' ? '#16a34a' : 'var(--ink-2)' }}; font-weight: 700;">{{ \App\Support\Money::format($asset->current_value) }} <small>ج.م</small></td>
  <td class="num">
    @php
      $depreciated = $asset->purchase_cost - $asset->current_value;
    @endphp
    @if($depreciated > 0)
      <span style="color: #ef4444">{{ \App\Support\Money::format($depreciated) }} <small>ج.م</small></span>
    @else
      <span class="muted">—</span>
    @endif
  </td>
  <td>
    @if($asset->notes)
      <div style="font-size: 12px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $asset->notes }}">
        {{ $asset->notes }}
      </div>
    @else
      <span class="muted">—</span>
    @endif
    <div class="muted" style="font-size: 11px;">تاريخ: {{ \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') }}</div>
  </td>
  @if($asset->status == 'active' || !isset($hideActions))
  <td style="text-align: center" onclick="event.stopPropagation()">
    <div style="display: flex; gap: 4px; justify-content: center;">
      <!-- Delete -->
      <button type="button" class="btn-icon btn-delete" title="حذف الأصل" onclick="openDeleteModal({{ $asset->id }}, '{{ addslashes($asset->name) }}')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
      </button>

      @if($asset->status == 'active')
        <!-- Depreciate -->
        <button type="button" class="btn-icon btn-depreciate" title="إهلاك" onclick="openDepreciateModal({{ $asset->id }}, '{{ addslashes($asset->name) }}', {{ $asset->current_value }})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        </button>

        <!-- Sell -->
        <button type="button" class="btn-icon btn-sell" title="بيع / تسييل" onclick="openSellModal({{ $asset->id }}, '{{ addslashes($asset->name) }}', {{ $asset->current_value }})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </button>

        <!-- Edit -->
        <button type="button" class="btn-icon btn-edit" title="تعديل" onclick="openEditModal({{ $asset->id }}, '{{ addslashes($asset->name) }}', {{ $asset->current_value ?? 0 }}, {{ $asset->purchase_cost ?? 0 }})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-tool"/></svg>
        </button>
      @endif
    </div>
  </td>
  @endif
</tr>
