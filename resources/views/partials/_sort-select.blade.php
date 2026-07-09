{{-- منتقي الترتيب المشترك — يُستخدم داخل أي فورم GET (فلتر موجود أو مستقل).
     البارامترات:
       $options : array مفتاحه القيمة المُرسَلة في sort وقيمته النص المعروض
       $current : القيمة الحالية (افتراضي: request('sort'))
       $default : أول اختيار لو مفيش sort في الرابط (اختياري، للمقارنة البصرية فقط) --}}
@php
  $current = $current ?? request('sort', $default ?? array_key_first($options));
@endphp
<div class="f-field">
  <label>الترتيب</label>
  <div class="f-select-wrap">
    <select name="sort" class="f-select" onchange="this.form.submit()">
      @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ $current === $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
  </div>
</div>
