(() => {
  'use strict';
  const checkout = document.querySelector('[data-payment-form]');
  if (checkout) {
    const panel = checkout.querySelector('[data-bank-panel]');
    const update = () => {
      const method = checkout.querySelector('[name=payment_method]:checked')?.value;
      const bank = method === 'BANK' || method === 'PROMPTPAY';
      if (panel) {
        panel.hidden = !bank;
        const input = panel.querySelector('[data-slip-input]');
        const now = panel.querySelector('[data-attach-now]')?.checked ?? true;
        input.required = bank && now; input.disabled = !bank || !now;
        const upload = panel.querySelector('[data-slip-now]'); if (upload) upload.hidden = !now;
        const bankDetails = panel.querySelector('[data-bank-details]'); if (bankDetails) bankDetails.hidden = method !== 'BANK';
        const ppDetails = panel.querySelector('[data-promptpay-details]'); if (ppDetails) ppDetails.hidden = method !== 'PROMPTPAY';
      }
    };
    checkout.querySelectorAll('[name=payment_method]').forEach(radio => radio.addEventListener('change', update));
    checkout.querySelector('[data-attach-now]')?.addEventListener('change', update);
    update();
  }
  document.querySelectorAll('[data-slip-form] [data-slip-input]').forEach(input => input.required = true);
  document.querySelectorAll('[data-slip-drop]').forEach(zone => {
    const input = zone.querySelector('[data-slip-input]');
    const preview = zone.querySelector('[data-slip-preview]');
    const error = zone.querySelector('[data-slip-error]');
    let imageUrl;
    const show = () => {
      if (imageUrl) URL.revokeObjectURL(imageUrl);
      preview.hidden = true; preview.removeAttribute('src'); error.textContent = '';
      const file = input.files[0]; if (!file) return;
      if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
        error.textContent = window.bpTranslate('เลือกไฟล์ JPG, PNG หรือ WEBP ขนาดไม่เกิน 5 MB'); input.value = ''; return;
      }
      imageUrl = URL.createObjectURL(file); preview.src = imageUrl; preview.hidden = false;
    };
    input.addEventListener('change', show);
    ['dragenter', 'dragover'].forEach(name => zone.addEventListener(name, event => { event.preventDefault(); if (!input.disabled) zone.classList.add('is-dragging'); }));
    ['dragleave', 'drop'].forEach(name => zone.addEventListener(name, event => { event.preventDefault(); zone.classList.remove('is-dragging'); }));
    zone.addEventListener('drop', event => {
      if (input.disabled || !event.dataTransfer.files.length) return;
      const transfer = new DataTransfer(); transfer.items.add(event.dataTransfer.files[0]); input.files = transfer.files; show();
    });
  });
  document.querySelectorAll('[data-copy-account]').forEach(button => button.addEventListener('click', async () => {
    const number = button.parentElement.querySelector('[data-account-number]').textContent.trim();
    try {
      if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(number);
      else {
        const temporary = document.createElement('textarea'); temporary.value = number; temporary.style.position = 'fixed'; temporary.style.opacity = '0'; document.body.append(temporary); temporary.select();
        const copied = document.execCommand('copy'); temporary.remove(); if (!copied) throw new Error('Clipboard unavailable');
      }
      button.textContent = window.bpTranslate('คัดลอกแล้ว');
    } catch { button.textContent = window.bpTranslate('กรุณาเลือกเลขบัญชีแล้วคัดลอก'); }
    button.setAttribute('aria-live', 'polite');
  }));
})();
