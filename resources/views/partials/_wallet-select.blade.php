{{-- مُنتقي المحفظة — يُستخدم في كل فورمات الصرف/التحصيل بعد دمج السيستمين.
     البارامترات:
       $wallets   : Collection من App\Models\Account::selectable()  (مطلوب)
       $name      : اسم الحقل (افتراضي account_id)
       $selected  : id المحفظة المختارة مسبقًا (اختياري)
       $label     : عنوان الحقل (افتراضي "المحفظة")
       $hint      : سطر توضيحي صغير تحت الحقل (اختياري)
       $selectClass, $selectStyle : تنسيق إضافي (اختياري)
       $bare      : true → يطبع الـ <select> فقط من غير label/wrapper --}}
@php
  $name        = $name        ?? 'account_id';
  $selected    = $selected    ?? old($name);
  $label       = $label       ?? 'المحفظة';
  $hint        = $hint        ?? null;
  $selectClass = $selectClass ?? '';
  $selectStyle = $selectStyle ?? '';
  $bare        = $bare        ?? false;
  $required    = $required    ?? false;
  $grouped     = collect($wallets ?? [])->groupBy(fn ($w) => $w->categoryAr());
  $defaultId   = \App\Models\Account::WALLET_ID;
@endphp

@php
  $selectMarkup = view('partials._wallet-select-inner', compact('name','selected','selectClass','selectStyle','grouped','defaultId','required'))->render();
@endphp

@if($bare)
  {!! $selectMarkup !!}
@else
  <div class="wallet-field" style="display:flex;flex-direction:column;gap:4px">
    <label style="font-weight:700;font-size:.82rem;display:flex;align-items:center;gap:5px">
      <i class="fa fa-wallet" style="color:#4f46e5"></i> {{ $label }}
    </label>
    {!! $selectMarkup !!}
    @if($hint)
      <small style="color:#94a3b8;font-size:.72rem">{{ $hint }}</small>
    @endif
  </div>
@endif
