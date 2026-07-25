@if($errors->any())
  <div class="flash error" style="background: linear-gradient(135deg, #fef2f2, #fff1f2); border: 1px solid #fecaca; border-radius: 20px; padding: 20px 24px; margin-bottom: 20px; animation: shakeError 0.5s ease-in-out; box-shadow: 0 8px 25px rgba(220, 38, 38, 0.12);">
    <div style="display:flex; align-items:flex-start; gap:14px;">
      <div style="background:#dc2626; color:#fff; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: 0 4px 12px rgba(220,38,38,0.3);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#i-x"/></svg>
      </div>
      <div style="flex:1;">
        <div style="font-weight:800; color:#991b1b; font-size:1.05rem; margin-bottom:8px;">حدث خطأ — برجاء مراجعة البيانات</div>
        @foreach($errors->all() as $error)
          <div style="color:#b91c1c; font-size:0.92rem; padding:4px 0; display:flex; align-items:center; gap:6px;">
            <span style="color:#ef4444;">●</span> {{ $error }}
          </div>
        @endforeach
      </div>
    </div>
  </div>
  <style>
    @keyframes shakeError {
      0%, 100% { transform: translateX(0); }
      15% { transform: translateX(-8px); }
      30% { transform: translateX(8px); }
      45% { transform: translateX(-6px); }
      60% { transform: translateX(6px); }
      75% { transform: translateX(-3px); }
      90% { transform: translateX(3px); }
    }
  </style>
@endif
