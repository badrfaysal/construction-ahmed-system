@php
  $clients = \App\Models\Client::select('name', 'phone')->get();
  $suppliers = \App\Models\Supplier::select('name', 'phone')->get();
  $workers = \App\Models\BandWorker::select('name', 'phone')->get();
  
  $allContacts = collect()
    ->concat($clients)
    ->concat($suppliers)
    ->concat($workers)
    ->filter(fn($c) => !empty($c->name))
    ->unique('phone')
    ->values()
    ->toJson();
@endphp

<style>
.custom-autocomplete { position: relative; }
.custom-autocomplete-dropdown {
  position: absolute; top: 100%; left: 0; right: 0; max-height: 200px; overflow-y: auto;
  background: var(--bg-card, #fff); border: 1px solid var(--line, #e2e8f0);
  border-top: none; border-radius: 0 0 6px 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  z-index: 50; margin: 0; padding: 0; list-style: none; display: none;
}
.custom-autocomplete-dropdown.show { display: block; }
.custom-autocomplete-dropdown li {
  padding: 10px 15px; cursor: pointer; border-bottom: 1px solid var(--line, #e2e8f0);
  color: var(--text, #1e293b); display: flex; justify-content: space-between;
}
.custom-autocomplete-dropdown li:last-child { border-bottom: none; }
.custom-autocomplete-dropdown li:hover { background: var(--bg-hover, #f8fafc); }
.custom-autocomplete-dropdown li .sub { font-size: 12px; color: var(--muted, #64748b); }
</style>

<script>
const knownContactsList = {!! $allContacts !!};

function renderGlobalDropdown(inputEl, type) {
  const wrap = inputEl.closest('.custom-autocomplete');
  if (!wrap) return;
  let dropdown = wrap.querySelector('.custom-autocomplete-dropdown');
  if (!dropdown) {
    dropdown = document.createElement('ul');
    dropdown.className = 'custom-autocomplete-dropdown';
    wrap.appendChild(dropdown);
    
    document.addEventListener('click', e => {
      if (!wrap.contains(e.target)) dropdown.classList.remove('show');
    });
  }

  const val = inputEl.value.trim().toLowerCase();
  let matches = [];
  if (type === 'name') {
    matches = knownContactsList.filter(w => w.name && w.name.toLowerCase().includes(val));
  } else {
    matches = knownContactsList.filter(w => w.phone && w.phone.includes(val));
  }

  if (matches.length === 0) {
    dropdown.classList.remove('show');
    return;
  }

  dropdown.innerHTML = matches.map(w => {
    const main = type === 'name' ? w.name : w.phone;
    const sub = type === 'name' ? (w.phone || '') : (w.name || '');
    return `<li data-name="${w.name}" data-phone="${w.phone || ''}">
      <span>${main}</span>
      <span class="sub">${sub}</span>
    </li>`;
  }).join('');
  
  dropdown.querySelectorAll('li').forEach(li => {
    li.addEventListener('click', () => {
      if (type === 'name') {
        inputEl.value = li.dataset.name;
        autocompleteContactByName(inputEl);
      } else {
        inputEl.value = li.dataset.phone;
        autocompleteContactByPhone(inputEl);
      }
      dropdown.classList.remove('show');
    });
  });

  dropdown.classList.add('show');
}

function autocompleteContactByName(inputEl) {
  renderGlobalDropdown(inputEl, 'name');
  const name = inputEl.value.trim();
  const contact = knownContactsList.find(w => w.name === name);
  const container = inputEl.closest('form');
  const phoneInput = container.querySelector('input[name="phone"]');
  
  if (contact) {
    if (phoneInput && !phoneInput.value) phoneInput.value = contact.phone || '';
  }
}

function autocompleteContactByPhone(inputEl) {
  renderGlobalDropdown(inputEl, 'phone');
  const phone = inputEl.value.trim();
  const contact = knownContactsList.find(w => w.phone === phone);
  const container = inputEl.closest('form');
  const nameInput = container.querySelector('input[name="name"]');
  
  if (contact) {
    if (nameInput && !nameInput.value) {
      nameInput.value = contact.name || '';
      nameInput.setAttribute('readonly', 'readonly');
    } else if (nameInput && nameInput.value === contact.name) {
      nameInput.setAttribute('readonly', 'readonly');
    }
  } else {
    if (nameInput) nameInput.removeAttribute('readonly');
  }
}
</script>
