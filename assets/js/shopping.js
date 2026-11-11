(() => {
  'use strict';
  const money = cents => new Intl.NumberFormat('th-TH', {style: 'currency', currency: 'THB'}).format(cents / 100);
  const renderQr = element => {
    element.replaceChildren();
    const payload = element.dataset.qrPayload;
    if (!payload || !window.qrcodegen) {element.textContent = window.bpTranslate('ยังไม่มี QR กรุณาตรวจยอดชำระ'); return;}
    const qr = qrcodegen.QrCode.encodeText(payload, qrcodegen.QrCode.Ecc.MEDIUM);
    const border = 4, scale = 6;
    const canvas = document.createElement('canvas');canvas.width = canvas.height = (qr.size + border * 2) * scale;
    const context = canvas.getContext('2d');context.fillStyle = '#fff';context.fillRect(0, 0, canvas.width, canvas.height);context.fillStyle = '#000';
    for (let y = 0; y < qr.size; y++) for (let x = 0; x < qr.size; x++) if (qr.getModule(x,y)) context.fillRect((x + border) * scale,(y + border) * scale,scale,scale);
    canvas.setAttribute('role','img');canvas.setAttribute('aria-label',window.bpTranslate('QR PromptPay สำหรับยอดชำระนี้'));element.append(canvas);
  };
  document.querySelectorAll('[data-qr-payload]').forEach(renderQr);
  const form = document.querySelector('[data-payment-form]');if (!form) return;
  const syncCodVisibility = () => {
    const isCod = form.querySelector('[name=payment_method]:checked')?.value === 'COD';
    document.querySelectorAll('[data-cod-fee-row]').forEach(row => row.hidden = !isCod);
    const reason = form.querySelector('[data-cod-reason]');if (reason) reason.hidden = !isCod;
  };
  syncCodVisibility();
  const summary=document.querySelector('.shop-summary'), layout=form.parentElement, mobile=matchMedia('(max-width:760px)');
  const positionSummary=()=>{if(!summary)return;if(mobile.matches)form.insertBefore(summary,form.querySelector('.commerce-section'));else layout.append(summary);};
  mobile.addEventListener('change',positionSummary);positionSummary();
  const error = form.querySelector('[data-quote-error]');const submit = form.querySelector('.checkout-submit');let requestId = 0, timer, busy = false;
  const update = async () => {
    const current = ++requestId;busy = true;submit.disabled = true;error.textContent = window.bpTranslate('กำลังตรวจยอดและสิทธิ์ส่วนลด…');
    const data = new FormData();for(const key of ['csrf','province','shipping_service_id','coupon_codes','skip_auto','payment_method']){const input=form.elements[key];if(input&&(!['checkbox','radio'].includes(input.type)||input.checked))data.set(key,input.value);}
    try {
      const response = await fetch('/babyproducts/checkout_quote.php',{method:'POST',body:data,headers:{Accept:'application/json'}});
      if(response.status===403||response.redirected)throw new Error(window.bpTranslate('เซสชันหมดอายุ กรุณาเข้าสู่ระบบหรือเปิดหน้านี้ใหม่'));
      const result = await response.json();if(current!==requestId)return;
      if(!result.ok)throw new Error(result.message||window.bpTranslate('ตรวจยอดไม่สำเร็จ'));
      Object.entries(result.quote).forEach(([key,value])=>{if(typeof value==='number')document.querySelectorAll(`[data-money="${key}"]`).forEach(node=>node.textContent=money(value));});
      document.querySelectorAll('[data-tax-block]').forEach(node=>node.hidden=!result.quote.tax_registered);
      form.querySelector('[data-quote-token]').value=result.token;
      const list=document.querySelector('[data-applied-promotions]');list.replaceChildren();result.quote.applied.forEach(promotion=>{const li=document.createElement('li');li.textContent=`${promotion.name} − ${money(promotion.discount)}`;list.append(li);});
      const cod=form.querySelector('[name=payment_method][value=COD]');if(cod){cod.disabled=!result.cod_available;if(cod.disabled&&cod.checked){cod.checked=false;const fallback=[...form.querySelectorAll('[name=payment_method]')].find(radio=>!radio.disabled);if(fallback){fallback.checked=true;fallback.dispatchEvent(new Event('change'));}}}
      form.querySelector('[data-cod-reason]').textContent=result.cod_available?'':result.cod_reason;
      syncCodVisibility();
      form.querySelectorAll('[data-qr-payload]').forEach(element=>{element.dataset.qrPayload=result.promptpay_payload||'';renderQr(element);});
      busy=false;error.textContent=window.bpTranslate('ตรวจยอดล่าสุดแล้ว');submit.disabled=!form.querySelector('[name=payment_method]:checked:not(:disabled)');
    }catch(exception){if(current!==requestId)return;busy=false;error.textContent=exception.message;form.querySelector('[data-quote-token]').value='';submit.disabled=true;}
  };
  const debounce=()=>{clearTimeout(timer);timer=setTimeout(update,300);busy=true;submit.disabled=true;};
  form.querySelectorAll('[data-quote-change], [name=province]').forEach(input=>{input.addEventListener('change',debounce);if(input.tagName==='INPUT')input.addEventListener('input',debounce);});
  form.addEventListener('shipping-address-change',debounce);form.querySelector('[data-refresh-quote]').addEventListener('click',update);
  form.querySelectorAll('[name=payment_method]').forEach(radio=>radio.addEventListener('change',()=>{syncCodVisibility();debounce();}));
  form.addEventListener('submit',event=>{if(submit.disabled||!form.querySelector('[data-quote-token]').value){event.preventDefault();error.textContent=window.bpTranslate('กรุณาตรวจยอดให้สำเร็จก่อนยืนยัน');}});
  update();
})();
