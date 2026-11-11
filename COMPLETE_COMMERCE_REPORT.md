# รายงาน Baby Products ฉบับรวม — 10 พฤศจิกายน 2026

พัฒนาต่อจาก PHP/PDO เดิม ไม่สร้างร้านใหม่ ไม่สร้างบัญชีหรือคำสั่งซื้อทดสอบใน `babyproducts` สำรองไว้ที่ `backups/complete-commerce-20261110-102527/` ซึ่ง Apache ปิดการเข้าถึง

## ข้อมูลเดิม

ตรวจเทียบกับสำเนาก่อนแก้ไขแล้ว 10 ตารางตรงกัน: users, products, categories, orders, order_items, user_addresses, payment_settings, shipping_services, promotions, promotion_redemptions รวมถึง hash รหัสผ่านที่เทียบภายในเครื่องโดยไม่พิมพ์ออกมา

สมาชิกเดิม 7 บัญชี สินค้า 24 รายการ หมวดหมู่ 6 รายการ คำสั่งซื้อ 2 รายการ ชื่อ ราคา สต๊อกและรหัสผ่านเดิมยังอยู่ครบ เพิ่มโครงสร้าง/ค่าเริ่มต้นของคอลัมน์ใหม่เท่านั้น

## วิธีใช้

1. เปิด Apache/MySQL เข้า http://localhost/babyproducts/ กด Ctrl+F5 ครั้งแรก เลือก TH | EN ใน Header ภาษาเก็บใน Session
2. เลือกสินค้าและสี/ไซซ์เมื่อสินค้านั้นเปิดใช้รุ่น เพิ่มลงตะกร้า ไป Checkout เลือกที่อยู่เดิมหรือกรอกใหม่
3. กดเปิดแผนที่เมื่อต้องการ คลิก/ลากหมุด ค้นชื่อสถานที่ หรือกดขอตำแหน่งปัจจุบันได้ ไม่อนุญาตตำแหน่งก็สั่งซื้อด้วยที่อยู่ปกติได้ ที่อยู่และพิกัดถูกสำเนาไว้ในคำสั่งซื้อ
4. ติ๊กบันทึกที่อยู่เฉพาะเมื่อประสงค์ใช้ครั้งต่อไป “คูปองของฉัน” แสดงโค้ดที่ยังมีสิทธิ์โดยไม่จองสิทธิ์ล่วงหน้า
5. เลือก COD หรือช่องทางโอนที่ตั้งผู้รับเงินจริงแล้ว สรุปยอดมีส่วนลด ค่าส่ง ค่าบริการ COD และ VAT เฉพาะเมื่อเปิดอย่างมีสิทธิ์ ตรวจยอดซ้ำฝั่ง Server
6. สร้างคำสั่งซื้อก่อนโอน แล้วแนบสลิปในรายละเอียด JPG/JPEG/PNG/WebP สูงสุด 5 MB การแนบเป็นเพียงรอตรวจสอบ Admin ต้องตรวจรายการเงินจริง
7. ดู Timeline เลขพัสดุ วันที่จัดส่ง และเอกสารที่ออกแล้วใน “คำสั่งซื้อของฉัน” สมาชิกอื่นเข้าถึงที่อยู่ สลิป และเอกสารไม่ได้

## ตั้งค่าหลังร้าน

เข้าที่ http://localhost/babyproducts/admin/ ด้วย Admin เดิม

| เมนู | ตั้งค่าหรือดำเนินการ |
|---|---|
| ตั้งค่าชำระเงิน | เปิด/ปิด COD พื้นที่/ช่วงยอด ตั้งบัญชีธนาคารหรือผู้รับ PromptPay จริงพร้อมยืนยัน |
| สต๊อก / สี / ไซซ์ | ปรับจำนวนพร้อมเหตุผล เพิ่ม SKU รุ่น การแปลงเป็นแยกรุ่นต้องยืนยัน เพราะยอดพร้อมขายใช้ผลรวมรุ่นที่เปิดใช้แทนยอดเดิม ต้องตรวจนับแบ่งจำนวนจริงให้ครบ |
| ภาษา / ภาษีสินค้า | ชื่อ/รายละเอียดอังกฤษ การยกเว้น VAT และระดับเตือนใกล้หมด ไม่แก้ราคา/สต๊อกผ่านหน้านี้ |
| หมวดหมู่ | ชื่อ/รายละเอียดไทยและอังกฤษ ไม่มีอังกฤษแสดงต้นฉบับ |
| ตั้งค่าภาษี | ค่าเริ่มต้นไม่จด VAT เมื่อมีสิทธิ์จริงจึงกรอกเลขผู้เสียภาษี ชื่อ ที่อยู่ สาขา อัตรา/ช่วงวันที่ วิธีรวม/ไม่รวม VAT และเงื่อนไขขนส่ง ให้ผู้รับผิดชอบบัญชียืนยัน |
| บริการขนส่ง | ราคา เงื่อนไขส่งฟรี จังหวัด COD fee ระยะเวลาโดยประมาณ น้ำหนักสูงสุด Checkout แสดงข้อมูลเปรียบเทียบที่ตั้งไว้ |
| แผนข้อตกลงขนส่ง | บริษัท ผู้ติดต่อ ข้อเสนอ COD/นำส่งเงิน รับพัสดุ ประกัน คืนสินค้า ช่วงสัญญา ต้องยืนยันลงนามจริง ไม่ได้ทำสัญญาหรือเชื่อม API ให้อัตโนมัติ |
| คำสั่งซื้อ → กดเลขคำสั่งซื้อ | ตรวจสลิปกับธนาคาร อนุมัติ/ปฏิเสธ เปลี่ยนสถานะตามลำดับ บันทึกน้ำหนัก/ขนาด บริษัทและเลขพัสดุก่อนส่ง ยืนยัน COD หลังส่งสำเร็จเมื่อรับเงินจริง |

Admin ออกใบเสร็จหลังยืนยันรับเงินจริง ใบกำกับภาษีเฉพาะคำสั่งซื้อที่มี snapshot VAT และข้อมูลผู้ขาย/ผู้ซื้อครบ และร้านยังมีสิทธิ์ออก เก็บ snapshot เอกสารแบบไม่เขียนทับ การแก้ข้อมูลภายหลังไม่เปลี่ยนเอกสารเดิม สรุปคำสั่งซื้อระบุว่าไม่ใช่ใบเสร็จหรือใบกำกับภาษี

คำนวณเงินด้วยจำนวนเต็มหน่วยสตางค์ เก็บฐานข้อมูล DECIMAL กระจายส่วนลดเข้ารายการก่อนภาษี แยกรายการยกเว้น ค่าจัดส่งรวม COD fee เป็นฐานขนส่งเดียวเมื่อเปิดให้เสีย VAT ปัดครึ่งขึ้นต่อรายการสินค้าและฐานขนส่ง ราคารวมภาษีแยก VAT ออกไม่บวกซ้ำ ราคาไม่รวมจึงบวกเพิ่ม หากอัตราปัจจุบันหาย/ซ้อนกันจะหยุดให้ร้านตรวจการตั้งค่า

## บริการภายนอกและข้อจำกัด

- Leaflet 1.9.4 self-host พร้อม BSD-2-Clause license โหลด OSM tiles หลังเปิดแผนที่ มี attribution ลิงก์ผู้ให้บริการ ค้น Photon เมื่อกดค้นเท่านั้น ไม่ทำ autocomplete จำกัดรวม 1 ครั้ง/วินาที แคชหนึ่งวัน ไม่มีการขอตำแหน่งก่อนกดปุ่มหรือการติดตามเบื้องหลัง
- OSM standard tiles/Photon public demo ไม่มี SLA ก่อนใช้จริงให้เจ้าของระบบเลือกผู้ให้บริการที่รองรับปริมาณงาน ตั้ง `PHOTON_ENDPOINT` เป็น HTTPS endpoint ที่เข้ากันได้ และ `MAP_TILE_URL` เป็น template `{z}/{x}/{y}` ที่มีสิทธิ์ใช้ พร้อมปรับ attribution ใน location.js รีสตาร์ต Apache หลังตั้ง environment เก็บ API keys ฝั่ง server ตามคู่มือผู้ให้บริการ ห้ามใส่ secret ใน JS/Git ค่าเริ่มต้นนี้ไม่ต้องใช้ API key
- อ้างอิงนโยบายผู้ให้บริการ: https://operations.osmfoundation.org/policies/tiles/ และ https://github.com/komoot/photon
- บัตร/e-Wallet และตรวจสลิปอัตโนมัติยังไม่เปิด มี Gateway scaffold และ interface แต่ไม่มี provider ใน registry ต้องเชื่อม Hosted Checkout และตรวจ signed Webhook จริงก่อนเปิด ไม่เก็บ PAN/CVV
- สลิปซ้ำตรวจ SHA-256 หลัง decode/encode เป็น PNG จับภาพเดิมและเปลี่ยนชื่อ ไม่ใช่ OCR/ตรวจรายการธนาคาร และไม่รับประกันจับภาพที่ตัดต่อหรือเปลี่ยนขนาด
- VAT/ใบกำกับภาษีปิดเป็นค่าเริ่มต้น ต้องให้ผู้รับผิดชอบบัญชีตรวจอัตรา ส่วนลด ขนส่ง การยกเว้น และเอกสารตามกิจการจริง ไม่ใช่ e-Tax Invoice ที่รับรองแล้ว อัตรา 7% ช่วง 1 ต.ค. 2026–30 ก.ย. 2027 อ้างอิงประกาศรัฐบาล แต่ไม่เปิดแทนร้าน: https://www.thaigov.go.th/th/news/166993
- หากเปลี่ยนสินค้าที่มีคำสั่งซื้อเก่าเป็นแยกรุ่น แล้วคืนจากคำสั่งซื้อที่ไม่มีรุ่น จะบันทึก `unallocated_return` และแจ้ง Admin ตรวจนับเข้ารุ่น ไม่สุ่มเลือกรุ่น
- ไม่สร้างคำแปลข้อมูลสินค้าที่ไม่มีจริง มีช่อง Admin กรอกภาษาอังกฤษและ fallback ต้นฉบับ ข้อความอิสระและหน้าบริหารบางส่วนยังเป็นไทย
- ยังไม่มี Carrier API/สัญญา/ออก label อัตโนมัติ Admin บันทึกเลขพัสดุและสถานะเอง ไม่ได้ทดสอบส่งพัสดุหรือรับเงินจริง
- ยังเป็น localhost/XAMPP ต้องจัด HTTPS โฮสติ้ง MySQL จำกัดสิทธิ์ สำรอง/กู้คืน ติดตามข้อผิดพลาด และนโยบาย/อายุเก็บพิกัดกับสลิปที่อนุมัติ ก่อนรับเงินจริง

## ไฟล์ที่สร้าง

- `includes/inventory.php`, `coordinates.php`, `location_picker.php`, `taxes.php`, `documents.php`, `i18n.php`, `slip_verifier.php`, `variant_select.php`, `document_controls.php`
- `map_search.php`, `language.php`, `coupons.php`, `order_document.php`
- `admin/inventory.php`, `product_options.php`, `taxes.php`, `shipping_agreements.php`
- `lang/th.php`, `en.php`, `en-extra.php`, `en-policies.php`, `en-validation.php`
- `assets/js/location.js`, `assets/css/complete-commerce.css`, `assets/vendor/leaflet/`
- `tools/migrate_complete_commerce.php`, `migrate_slip_fingerprints.php`, `map_provider_check.php`
- ชุดทดสอบ `complete_commerce.php`, `complete_browser.ps1`, `commerce_preservation.php`, `inventory_race_worker.php`, `map_search_live.php` และรายงานนี้

## ไฟล์เดิมที่เชื่อมต่อหรือปรับภาษา

`cart.php`, `checkout.php`, `checkout_quote.php`, `addresses.php`, `profile.php`, `products.php`, `product_detail.php`, `my_orders.php`, `notifications.php`, `privacy.php`, Login/Register/Contact/Terms/404 และส่วนประกอบ `includes/functions.php`, `header.php`, `footer.php`, `commerce.php`, `payments.php`, `addresses.php`, `checkout_addresses.php`, `checkout_commerce.php`, `checkout_summary.php`, `order_view.php`, `order_commerce.php`, `seo.php`, `boutique_home.php`, `slip_input.php`

JS เดิม `script.js`, `addresses.js`, `payments.js`, `shopping.js`; Admin `bootstrap.php`, `product_edit.php`, `categories.php`, `shipping.php`, `order_detail.php`, `index.php`, `reports.php` รวมถึงข้อความ UI บางส่วนของหน้า Admin อื่นที่ใช้ dictionary

## ฐานข้อมูล

Migration: `database/migrations/20261110_complete_commerce.sql`, `20261110_slip_fingerprints.sql` เพิ่ม 8 ตาราง product_variants, inventory_movements, tax_settings, tax_rates, order_documents, shipping_agreements, map_search_cache, slip_fingerprints พร้อมคอลัมน์ภาษา รุ่น พิกัด ภาษี COD fee ขนส่ง และ hash สลิป รวมปัจจุบัน 30 ตาราง ไม่ลบข้อมูลเดิม

`database.sql` สำหรับติดตั้งใหม่อัปเดตแล้ว ห้าม import ซ้ำลงฐานใช้งาน เครื่องนี้รัน migration แล้ว เครื่องอื่นให้สำรองแล้วรัน `C:\xampp\php\php.exe tools\migrate_complete_commerce.php` จากโครงการ เครื่องมือรันซ้ำโดยไม่รีเซ็ตค่าร้าน และเติม hash สลิปเดิมที่ยังมีไฟล์โดยไม่เปลี่ยนสถานะรับเงิน

## ผลทดสอบ

ทดสอบรอบสุดท้ายใน `babyproducts_test_payments_complete_20261110b` แยกจากข้อมูลร้าน เก็บ log ในโฟลเดอร์สำรอง ไม่มีการพิมพ์รหัสผ่าน

| ชุดทดสอบ | ผ่าน |
|---|---:|
| ระบบเดิม Login/Register/ตะกร้า/COD/Admin | 65 |
| การชำระเงิน/สลิป/สิทธิ์/ไฟล์อันตราย/คืนเงิน | 51 |
| สมุดที่อยู่ | 34 |
| สั่งซ้ำ/ที่อยู่ถูกลบ | 4 |
| โปรโมชั่น COD PromptPay ราคา Server | 46 |
| กรณีขอบโปรโมชั่น/กระจายสตางค์ | 7 |
| Gateway scaffold ลายเซ็น/ยอด/รายการซ้ำด้วย QA adapter ที่ไม่ลงทะเบียนจริง | 5 |
| รุ่น/ledger/พิกัด/VAT/เอกสาร/สลิปซ้ำ/ภาษา/ขนส่ง/สอง PDO แย่งชิ้นสุดท้าย | 77 |
| ค้นสถานที่จริงผ่าน authenticated proxy + verified HTTPS | 1 |
| Chrome responsive/UI | 32 |
| เทียบข้อมูลเดิม 10 ตาราง | 10 |
| **รวม** | **332** |

Chrome ตรวจหน้าลูกค้า Checkout/ที่อยู่/คูปอง/คำสั่งซื้อ/หน้าแรก และ Admin inventory/tax/ขนส่ง/แผนข้อตกลง ที่ 1440, 768, 390 พิกเซล ไม่มีการล้นระดับหน้าหรือ JavaScript error ตรวจเปลี่ยน COD fee ตามวิธีชำระ คลิก/ลาก/ล้างหมุด ปฏิเสธ geolocation และอังกฤษ ภาพหน้าจอ 28 ไฟล์ใน `backups/complete-commerce-20261110-102527/screenshots/` ใน automation บล็อก tile ภายนอกเพื่อลด traffic จึงไม่ได้ยืนยันภาพ tile จากผู้ให้บริการเสมอไป ตรวจ Photon จริงแยกแล้ว HTTP 200 คืนสถานที่ได้

ตรวจ syntax PHP 128 ไฟล์นอก backups ผ่าน ทดสอบจริงเพิ่มเติม: สต๊อกสุดท้ายสำเร็จเพียงหนึ่งจากสอง process อีก process ถูกปฏิเสธ ไม่มีติดลบ คืนสต๊อกครั้งเดียว ชำระไม่หักซ้ำ VAT รวม/ไม่รวม/ยกเว้น/คูปอง/หมดอายุ เอกสารไม่เขียนทับและเป็นส่วนตัว ตรวจ backfill hash ซ้ำสองรอบแล้วสถานะชำระไม่เปลี่ยนและไม่เตือนภาพเดิมของรายการเดียวกันผิด ไม่ได้ทดสอบรับเงินจริง ส่งพัสดุจริง หรือรับรองเอกสารภาษี

ลบรหัสเข้าบัญชี QA ที่สุ่มใช้ใน Chrome ออกจากไฟล์ชั่วคราวหลังทดสอบแล้ว ชุดทดสอบสร้างใหม่ได้เมื่อรันบนฐานทดสอบตาม guard เพิ่ม .gitignore ป้องกัน backups, private storage, .env และ log เข้าระบบ Git
