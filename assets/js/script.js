document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!window.confirm(form.dataset.confirm))event.preventDefault();}));

document.querySelectorAll('nav a').forEach(link => {
    const path = location.pathname.replace(/index\.php$/, '');
    const targetUrl = new URL(link.href);
    const target = targetUrl.pathname.replace(/index\.php$/, '');
    const currentFeatured = new URL(location.href).searchParams.get('featured') === '1';
    const targetFeatured = targetUrl.searchParams.get('featured') === '1';
    const productsMatch = target.endsWith('/products.php') && ((path === target && currentFeatured === targetFeatured) || (path.endsWith('/product_detail.php') && !targetFeatured));
    const otherMatch = path === target && !target.endsWith('/products.php');
    if (productsMatch || otherMatch) link.setAttribute('aria-current', 'page');
    else link.removeAttribute('aria-current');
});

document.addEventListener('error', event => {
    const image = event.target;
    if (!(image instanceof HTMLImageElement) || !image.hasAttribute('data-product-image')) return;
    if (image.dataset.fallbackUsed) return;
    image.dataset.fallbackUsed = 'true';
    image.removeAttribute('srcset');
    image.src = image.dataset.fallback;
    image.alt += window.bpTranslate(' — ภาพสำรองหมวดหมู่');
    const container = image.closest('.modern-product, .product-photo, .cart-product, .admin-product-photo');
    const note = container?.querySelector('.photo-note, figcaption, small');
    if (note) note.textContent = window.bpTranslate('ภาพสำรองหมวดหมู่ — ยังไม่มีภาพเฉพาะสินค้า');
}, true);

document.querySelectorAll('[data-image-upload]').forEach(input => {
    let previewUrl;
    input.addEventListener('change', () => {
        const preview = input.form.querySelector('[data-upload-preview]');
        const file = input.files?.[0];
        input.setCustomValidity('');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        preview.hidden = true;
        if (!file) return;
        if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            input.setCustomValidity('เลือกรูป JPEG PNG หรือ WebP ขนาดไม่เกิน 5 MB');
            input.reportValidity();
            return;
        }
        input.setCustomValidity('');
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
    });
});

// Copied email addresses can contain invisible formatting characters.
// Clean email fields before native browser validation; never touch passwords.
document.querySelectorAll('input[type="email"], input[data-email]').forEach(input => {
    const clean = () => {
        const value = input.value.replace(/[\u200B-\u200F\u202A-\u202E\u2060-\u2069\uFEFF]/g, '').trim();
        if (value !== input.value) input.value = value;
    };
    ['input', 'change', 'blur'].forEach(event => input.addEventListener(event, clean));
    input.form?.addEventListener('click', clean, true);
    input.form?.addEventListener('keydown', event => {
        if (event.key === 'Enter') clean();
    }, true);
    clean();
});

// Storefront interactions use progressive enhancement: forms still work without JS.
(() => {
    document.documentElement.classList.add('shop-interactive');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const hero = document.querySelector('[data-hero]');
    if (hero) {
        const slides = [...hero.querySelectorAll('[data-slide]')];
        const dots = [...hero.querySelectorAll('[data-slide-to]')];
        let selected = 0;
        const show = index => {
            selected = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => { slide.hidden = i !== selected; });
            dots.forEach((dot, i) => { dot.classList.toggle('active', i === selected); dot.setAttribute('aria-pressed', String(i === selected)); });
            hero.querySelector('[data-slide-counter]').textContent = `${String(selected + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
        };
        dots.forEach(dot => dot.addEventListener('click', () => show(Number(dot.dataset.slideTo))));
        hero.querySelectorAll('[data-slide-step]').forEach(button => button.addEventListener('click', () => show(selected + Number(button.dataset.slideStep))));
    }

    const tabs = [...document.querySelectorAll('[data-shop-tab]')];
    const selectTab = tab => {
        tabs.forEach(button => { const active = button === tab; button.setAttribute('aria-selected', String(active)); button.tabIndex = active ? 0 : -1; });
        document.querySelectorAll('[data-shop-panel]').forEach(panel => { panel.hidden = panel.dataset.shopPanel !== tab.dataset.shopTab; });
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', event => {
            const offset = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
            let next = offset ? tabs[(index + offset + tabs.length) % tabs.length] : null;
            if (event.key === 'Home') next = tabs[0];
            if (event.key === 'End') next = tabs[tabs.length - 1];
            if (next) { event.preventDefault(); selectTab(next); next.focus(); }
        });
    });

    const toast = document.createElement('div');
    toast.className = 'site-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.append(toast);
    let toastTimer;
    const notify = message => {
        clearTimeout(toastTimer);
        toast.textContent = message;
        toast.classList.add('visible');
        toastTimer = setTimeout(() => toast.classList.remove('visible'), 4500);
    };

    document.querySelectorAll('[data-quick-add]').forEach(form => form.addEventListener('submit', async event => {
        if (!window.fetch) return;
        event.preventDefault();
        if (form.dataset.busy === 'true') return;
        form.dataset.busy = 'true';
        const button = form.querySelector('button');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = '…';
        try {
            const response = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!(response.headers.get('content-type') || '').includes('application/json')) throw new Error('Unexpected response');
            const result = await response.json();
            notify(result.message || window.bpTranslate('ไม่สามารถเพิ่มสินค้าได้ กรุณาตรวจตะกร้า'));
            if (response.ok && result.ok) {
                document.querySelectorAll('[data-cart-count]').forEach(count => {
                    count.textContent = String(result.count);
                    count.classList.remove('pop');
                    void count.offsetWidth;
                    count.classList.add('pop');
                });
            }
        } catch {
            notify(window.bpTranslate('ไม่สามารถยืนยันผลได้ กรุณาเปิดตะกร้าตรวจสอบก่อนลองอีกครั้ง'));
        } finally {
            form.dataset.busy = 'false';
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.textContent = '+';
        }
    }));

    if (typeof HTMLDialogElement !== 'undefined') {
        const dialog = document.createElement('dialog');
        dialog.className = 'product-dialog';
        dialog.setAttribute('aria-labelledby', 'quick-product-title');
        dialog.innerHTML = '<button type="button" class="dialog-close" aria-label="ปิดรายละเอียดสินค้า">×</button><div class="dialog-layout"><div class="dialog-image"><img alt=""></div><div class="dialog-copy"><span class="eyebrow">รายละเอียดสินค้าโดยย่อ</span><h2 id="quick-product-title"></h2><div class="price"></div><p class="dialog-description"></p><small class="dialog-stock"></small><a class="button">ดูรายละเอียดและเลือกจำนวน ↗</a></div></div>';
        if(window.bpLanguage==='en'){dialog.querySelector('.dialog-close').setAttribute('aria-label','Close product details');dialog.querySelector('.eyebrow').textContent='Product at a glance';dialog.querySelector('a').textContent='View details and choose quantity ↗';}document.body.append(dialog);
        let opener;
        dialog.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); } });
        dialog.addEventListener('close', () => opener?.focus({ preventScroll: true }));
        document.querySelectorAll('[data-quick-view]').forEach(button => button.addEventListener('click', () => {
            try {
                const product = JSON.parse(button.dataset.quickView);
                opener = button;
                dialog.querySelector('h2').textContent = product.name;
                const image = dialog.querySelector('img');
                image.src = product.image;
                image.alt = product.name;
                dialog.querySelector('.price').textContent = product.price;
                dialog.querySelector('.dialog-description').textContent = product.description;
                dialog.querySelector('.dialog-stock').textContent = `${window.bpTranslate('คงเหลือ')} ${product.stock} ${window.bpTranslate('ชิ้น')} · ${product.image_note || 'Demo product'}`;
                dialog.querySelector('a').href = product.url;
                if (!dialog.open) dialog.showModal();
            } catch { notify(window.bpTranslate('เปิดรายละเอียดแบบย่อไม่ได้ กรุณากดที่ชื่อสินค้า')); }
        }));
    }

    if ('IntersectionObserver' in window && !reducedMotion) {
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
        }), { threshold: 0.05 });
        document.querySelectorAll('[data-reveal]').forEach(section => { section.classList.add('reveal-ready'); observer.observe(section); });
    }
    const backTop = document.createElement('button');
    backTop.className = 'back-top';
    backTop.type = 'button';
    backTop.textContent = '↑';
    backTop.setAttribute('aria-label', window.bpTranslate('กลับขึ้นด้านบน'));
    backTop.hidden = true;
    backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reducedMotion ? 'instant' : 'smooth' }));
    document.body.append(backTop);
    const updateScroll = () => { backTop.hidden = window.scrollY < 500; };
    window.addEventListener('scroll', updateScroll, { passive: true });
    updateScroll();
})();
