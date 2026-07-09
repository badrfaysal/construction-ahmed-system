{{-- الـ <select> نفسه — مفصول عشان يتقدر يتطبع سواء لوحده (bare) أو جوّه wrapper.
     لو $required=true بيبقى إجباري، والخيار الأول placeholder مِتعطّل عشان يجبر المستخدم يختار. --}}
@php $required = $required ?? false; @endphp
<select name="{{ $name }}"
        class="wallet-select {{ $selectClass }}"
        @if($required) required @endif
        style="padding:8px 12px;border:1px solid #e4eaf4;border-radius:8px;font-size:.87rem;background:#fff;min-width:180px;{{ $selectStyle }}">
  @if($required)
    <option value="" disabled {{ $selected ? '' : 'selected' }}>— اختر المحفظة —</option>
  @else
    <option value="">— المحفظة الافتراضية (المقاولات) —</option>
  @endif
  @foreach($grouped as $catLabel => $group)
    <optgroup label="{{ $catLabel }}">
      @foreach($group as $w)
        <option value="{{ $w->id }}"
          @selected((string) $selected === (string) $w->id)>
          {{ $w->account_name }}@if($w->id == $defaultId) ★@endif — {{ \App\Support\Money::format($w->balance) }} ج
        </option>
      @endforeach
    </optgroup>
  @endforeach
</select>
