(() => {
  const form = document.querySelector('[data-payment-form]');
  if (!form) return;
  const selected = form.querySelector('[data-saved-address-id]');
  const message = form.querySelector('[data-address-choice-status]');
  const fields = ['recipient', 'phone', 'address', 'province', 'district', 'subdistrict', 'postcode', 'map_url', 'address_note', 'latitude', 'longitude'];
  form.querySelectorAll('[data-address-choice]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    const address = JSON.parse(link.dataset.addressChoice);
    for (const key of fields) if (form.elements[key]) form.elements[key].value = address[key] || '';
    selected.value = address.id;
    form.querySelectorAll('.address-card').forEach(card => card.classList.remove('is-selected'));
    link.closest('.address-card').classList.add('is-selected');
    form.elements.save_address.checked = false;
    form.elements.save_as_default.checked = false;
    form.dispatchEvent(new Event('shipping-address-change'));
    message.textContent = window.bpTranslate('เลือกที่อยู่แล้ว ตรวจสอบข้อมูลด้านล่างก่อนยืนยันคำสั่งซื้อ');
  }));
  form.querySelector('[data-new-address]')?.addEventListener('click', event => {
    event.preventDefault();
    selected.value = '0';
    for (const key of fields) if (form.elements[key]) form.elements[key].value = '';
    form.querySelectorAll('.address-card').forEach(card => card.classList.remove('is-selected'));
    form.elements.save_address.checked = false;
    form.elements.save_as_default.checked = false;
    message.textContent = window.bpTranslate('กรอกที่อยู่ใหม่ จะบันทึกในบัญชีเฉพาะเมื่อเลือกบันทึกไว้ใช้ครั้งต่อไป');
    form.elements.recipient.focus();
    form.dispatchEvent(new Event('shipping-address-change'));
  });
})();
