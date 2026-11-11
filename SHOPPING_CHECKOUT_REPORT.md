# Checkout ขนส่ง โปรโมชั่น และช่องทางชำระเงิน

ดำเนินการวันที่ 10 พฤศจิกายน 2026 บน Baby Products เดิม PHP/PDO/MySQL ไม่สร้างเว็บไซต์ใหม่

## ใช้งานของลูกค้า

1. เพิ่มสินค้าในตะกร้า เข้าสู่ระบบ แล้วเปิด http://localhost/babyproducts/checkout.php
2. เลือกที่อยู่ที่บันทึกไว้หรือกรอกข้อมูลใหม่ ระบบที่อยู่เดิมยังใช้งานได้ ไม่บันทึกใหม่หากไม่เลือกยืนยัน
3. เลือกบริการขนส่ง กรอกคูปองที่มีสิทธิ์ แล้วกดตรวจยอด ระบบคำนวณจาก Server และแสดงยอดรวมสินค้า ส่วนลดสินค้า ส่วนลดคูปอง ค่าจัดส่ง ส่วนลดค่าจัดส่ง และยอดสุทธิ แสดงรูปสินค้า จำนวน และราคาประกอบ
4. COD เลือกได้เมื่อร้านเปิดและผ่านพื้นที่/บริการขนส่ง/ยอดสุทธิที่กำหนด ไม่ถือว่ารับเงินจนกว่า Admin บันทึกรับเงินจริง
5. BANK แสดงเฉพาะเมื่อตั้งบัญชีจริงและเปิดใช้งาน คัดลอกเลขบัญชีได้ สร้างคำสั่งซื้อก่อน แล้วแนบสลิปภายหลังได้ หากโอนแล้วสามารถเลือกแจ้งชำระตอน Checkout และแนบไฟล์ทันที
6. PromptPay แสดงเฉพาะเมื่อตั้งผู้รับจริงและเปิดใช้งาน สร้างคำสั่งซื้อก่อนเพื่อจองราคาและส่วนลด จากนั้นเปิด QR ระบุยอดในรายละเอียดคำสั่งซื้อ ตรวจชื่อผู้รับในแอปธนาคารก่อนโอน แล้วแนบหลักฐาน
7. สลิป JPG/JPEG/PNG/WEBP ไม่เกิน 5 MB มี preview/drag-drop และตรวจเนื้อหาจริง การส่งหลักฐานขึ้น pending_review ไม่ใช่ paid หลังปฏิเสธสามารถส่งใหม่ได้

มือถือย้ายการ์ดสรุปสินค้าและราคาให้อยู่ก่อนส่วนเลือกขนส่ง/จ่ายเงิน พร้อมปุ่มยืนยันติดขอบล่างที่มีตัวเลขยอดสุทธิ Desktop ใช้การ์ดสรุปทางด้านขวา ไม่ใช้โลโก้หรือหน้าตาเฉพาะของแพลตฟอร์มอื่น

## ตั้งค่าของ Admin

- http://localhost/babyproducts/admin/payment_settings.php — เปิด/ปิด COD, ยอดสุทธิขั้นต่ำ/สูงสุด, จังหวัดที่รองรับ และข้อมูล BANK/PromptPay
- จังหวัดให้ใส่จังหวัดละบรรทัดหรือคั่นด้วย comma เว้นว่างหมายถึงทุกจังหวัด ต้องตรงกับชื่อจังหวัดที่ลูกค้ากรอก เช่น “เลย”
- PromptPay รองรับเบอร์มือถือ 10 หลักหรือเลขประชาชน 13 หลักที่ผ่าน checksum พร้อมชื่อผู้รับ ต้องเลือกยืนยันว่าเป็นผู้รับเงินจริง ไม่สร้างหมายเลขจำลองให้ร้าน
- http://localhost/babyproducts/admin/shipping.php — เพิ่ม/แก้ชื่อบริการ ค่าจัดส่ง ยอดหลังส่วนลดที่ได้ส่งฟรี จังหวัด และการรองรับ COD เปิดใช้งานเฉพาะบริการที่ร้านใช้จริง ปิดแทนการลบ
- http://localhost/babyproducts/admin/promotions.php — สร้างและแก้โปรโมชั่น/คูปอง เปิดใช้งานเมื่อพร้อม ไม่มีคูปองตัวอย่างเปิดให้เอง
- รายละเอียดคำสั่งซื้อใช้ระบบตรวจสลิปและประวัติเดิม รองรับ BANK และ PromptPay ต้องตรวจยอดเข้าจริงก่อนอนุมัติ

บริการมาตรฐานเดิม 50 บาท ฟรีเมื่อยอดสินค้าหลังส่วนลดครบ 1,000 บาท ถูกเก็บเป็นบริการเริ่มต้น ไม่มีการสมมติว่าเชื่อม API บริษัทขนส่งหรือซื้อใบปะหน้าพัสดุแล้ว เลขพัสดุยังบันทึกโดย Admin

## ประเภทโปรโมชั่นและกติกา

รองรับ fixed, percent, shipping, unit_price, flash_price และ bundle_price:

- จำนวนเงินหรือเปอร์เซ็นต์: คิดจากยอดสินค้าที่ร่วมและยังเหลือหลังส่วนลดก่อนหน้า
- คูปองส่งฟรี: ประเภท shipping ใส่ value=0 เพื่อยกเว้นค่าจัดส่งทั้งหมด หรือใส่จำนวนบาทเพื่อจำกัดส่วนลด
- ราคาพิเศษและ Flash Sale: value เป็นราคาต่อชิ้น มีวันเริ่ม/สิ้นสุดกำกับ Flash Sale เช่นกัน ไม่แก้ products.price เดิม
- ซื้อหลายชิ้น: min_quantity เป็นจำนวนชิ้นต่อกลุ่ม value เป็นราคาของหนึ่งกลุ่ม คิดกลุ่มแยกตาม SKU ไม่คละสินค้า เศษชิ้นคิดตามราคาปกติที่ยังเหลือหลังส่วนลดก่อนหน้า
- เว้น code ว่างเป็นโปรอัตโนมัติ ใส่ code เช่น BABY50 เป็นคูปอง ประเภทส่งฟรีต้องมีโค้ด
- เลือกโปรอัตโนมัติที่ลดสินค้าได้มากที่สุด 1 รายการ ผูกคูปองได้ไม่เกิน 2 โค้ด ถ้าจะใช้ร่วม ทุกรายการต้องเปิด stackable คูปองใช้ตามลำดับที่กรอก (คั่น comma)
- หากโปรอัตโนมัติที่ถูกเลือกใช้ร่วมไม่ได้ ลูกค้าลบคูปองหรือเลือก “ไม่ใช้โปรโมชั่นอัตโนมัติ” ได้ ไม่มีการตัดส่วนลดทิ้งโดยไม่แจ้ง
- ยอดขั้นต่ำคูปองอิงยอดรวมก่อนส่วนลด ยอดขั้นต่ำส่งฟรีอิงยอดสินค้าเมื่อหักส่วนลดสินค้าและคูปองแล้ว
- กำหนดวันเวลาไทย จำนวนสิทธิ์รวม สิทธิ์ต่อสมาชิก ยอดขั้นต่ำ เพดานส่วนลด จำนวนชิ้น สินค้า หมวด และกลุ่มสมาชิกได้
- เมื่อเลือกทั้งสินค้าและหมวด ต้องตรงทั้งสองเงื่อนไข กลุ่มสมาชิกใหม่ = ยังไม่มีคำสั่งซื้อที่ไม่ยกเลิก สมาชิกเก่า = มีคำสั่งซื้อที่ไม่ยกเลิกแล้ว
- สิทธิ์หนึ่งครั้งหมายถึงหนึ่งคำสั่งซื้อ ไม่ใช่จำนวนชิ้น การเปิดดู/ตรวจยอดไม่จองสิทธิ์ สร้างคำสั่งซื้อจึงจอง และยกเลิกจึงคืนสิทธิ์ ไม่ลบประวัติใช้สิทธิ์เก่า
- คำสั่งซื้อที่ยังไม่ชำระยังถือสิทธิ์ไว้ ไม่มีระบบยกเลิก/หมดอายุคำสั่งซื้ออัตโนมัติในรอบนี้ Admin ต้องยกเลิกเมื่อไม่ดำเนินการต่อ

## ราคาและความปลอดภัย

คำนวณเป็นจำนวนเต็มสตางค์บน Server ตรวจสินค้าที่เปิดใช้งาน ราคา สต็อก บริการขนส่ง พื้นที่ COD และสิทธิ์โปรโมชั่นซ้ำใน Transaction ล็อกสินค้าและแถวโปรโมชั่นก่อนบันทึกสิทธิ์ และใช้ READ COMMITTED ใน Checkout เพื่อตรวจจำนวนใช้สิทธิ์ล่าสุดหลังได้ล็อก

แบ่งส่วนลดลงแต่ละบรรทัดโดยรักษาเศษสตางค์ ไม่ติดลบและไม่เกินยอดสินค้าที่ร่วม เก็บ line_subtotal/discount_amount/line_total และสำเนาโปรโมชั่น/ราคา/ขนส่ง/ผู้รับ PromptPay ไว้กับ orders ข้อมูลเดิมไม่เปลี่ยนเมื่อแก้โปรโมชั่นหรือผู้รับภายหลัง รายงานสินค้าขายดีใช้ line_total ใหม่ และ fallback ข้อมูลเก่าได้

หน้าลูกค้าใช้ quote_token ที่ Server เซ็นด้วย HMAC ผูกยอด/รายการส่วนลด/บริการกับบัญชี หากราคาหรือสิทธิ์เปลี่ยนก่อนบันทึกจะปฏิเสธและให้ตรวจยอดใหม่ ป้องกันส่งซ้ำด้วย token คำสั่งซื้อเดิม และจองโปรโมชั่นร่วม Transaction เดียวกับสต็อก ที่อยู่ สลิป และแจ้งเตือน

Admin ตรวจสิทธิ์และ CSRF ทุกการแก้ การเลือก/ค้นหาข้อมูลใช้ Prepared Statements ข้อมูลโปรโมชั่นที่สะท้อนใน HTML ถูก escape และ JavaScript ใช้ textContent ไม่มีการเชื่อค่าราคาจาก Browser

## PromptPay QR

สร้าง payload ฝั่ง Server ด้วย EMV TLV สำหรับ PromptPay Credit Transfer: AID A000000677010111, phone subtag 01 / national ID subtag 02, สกุล THB 764, จำนวนเงินแบบสตางค์ และ CRC16-CCITT-FALSE ไม่ใช่รูป QR ตกแต่งหรือบริการสร้าง QR ออนไลน์

อ้างอิงเอกสาร [Thai QR Payment Standard ของ ธปท.](https://www.bot.or.th/content/dam/bot/documents/th/our-roles/payment-systems/about-payment-systems/ThaiQRCode_Payment_Standard.pdf) และใช้ [QR encoder ของ Project Nayuki](https://www.nayuki.io/page/qr-code-generator-library) แบบ MIT เก็บไฟล์ในโครงการ checksum/แหล่งที่มาอยู่ assets/js/vendor/README.md

หลังส่งสลิปแล้วไม่แสดง QR ให้ชำระซ้ำระหว่างตรวจหลักฐาน ไม่อ้างว่า QR นี้เชื่อมตรวจเงินเข้าธนาคารอัตโนมัติ การทดสอบอ่าน QR กลับไม่เท่ากับการรับรองจากธนาคารหรือการทดสอบโอนเงินจริง เจ้าของร้านต้องทดสอบชื่อผู้รับและรับเงินของบัญชีจริงก่อนเปิดใช้งาน

## บัตร Mobile Banking และ e-Wallet ผ่าน Gateway

เตรียม interface HostedPaymentGateway, ตาราง gateway_sessions/gateway_events และ payment_webhook.php สำหรับ adapter จริงในอนาคต registry ปัจจุบันว่าง จึงไม่มีช่องทาง GATEWAY/บัตร/e-Wallet ให้ลูกค้าเลือก และ Webhook ที่ไม่มี provider จะตอบ 503 โดยไม่เปลี่ยนสถานะ

adapter ต้องสร้าง Hosted Checkout/Tokenized Session กับผู้ให้บริการจริง ตรวจ signature/timestamp/ความแท้ของ Webhook ตามคู่มือของ provider แล้วจึงส่ง event ที่ตรวจแล้วเข้าส่วนกลาง ส่วนกลางตรวจ provider/session/order, THB, ยอดเงินที่ตรงกัน และ event_id แบบ idempotent ก่อนเปลี่ยนเป็น paid ไม่ใช้หน้า redirect กลับจาก Gateway เป็นหลักฐานรับเงิน

ไม่มีช่องรับหมายเลขบัตรเต็ม/CVV ไม่มีตารางเก็บข้อมูลบัตร และไม่ใส่ secret ใน Git การเชื่อมจริงยังต้องเลือกผู้ให้บริการ เปิด merchant account ใส่กุญแจผ่าน environment และทดสอบ sandbox/production ของผู้ให้บริการ ไม่ใช่การเชื่อม Gateway สำเร็จในรอบนี้

## ไฟล์และ Migration

เพิ่ม includes/commerce.php, includes/promptpay.php, includes/gateway.php, includes/checkout_commerce.php, includes/checkout_summary.php, includes/order_commerce.php, includes/commerce_payment_settings.php, checkout_quote.php, payment_webhook.php, admin/promotions.php, admin/shipping.php, assets/css/shopping.css, assets/js/shopping.js, assets/js/vendor/qrcodegen.js และเอกสารที่มา

เพิ่ม database/migrations/20261110_shopping_checkout.sql และ tools/migrate_shopping_checkout.php รองรับ XAMPP MariaDB เพิ่ม shipping_services, promotions, promotion_products, promotion_categories, promotion_redemptions, gateway_sessions, gateway_events และคอลัมน์ราคา/ขนส่ง/PromptPay โดยไม่ลบตารางเดิม รวมฐานจริง 22 ตาราง

แก้ checkout.php, order_detail.php, my_orders.php, includes/functions.php, includes/header.php, includes/payments.php, includes/order_view.php, admin/payment_settings.php, admin/bootstrap.php, admin/order_detail.php, admin/reports.php, assets/js/payments.js, assets/js/addresses.js, database.sql, privacy.php, terms.php และข้อความค่าจัดส่งใน contact.php

เพิ่ม tests/shopping_integration.php, tests/commerce_edges.php, tests/gateway_scaffold.php, tests/shopping_browser_fixture.php, tests/shopping_browser.ps1 และ decoder/ใบอนุญาตใน tests/vendor แก้ fixture ของ tests/payments_integration.php และ tests/payment_browser.ps1 ให้ระบุการแจ้งชำระทันทีชัดเจนตาม UX ใหม่

สำรองก่อนแก้ไว้ใน backups/shopping-checkout-20261110-094546/ มี database-before.sql, database-after.sql และ screenshots/ ติดตั้ง migration บนเครื่องนี้แล้ว ไม่ต้องนำเข้า database.sql ซ้ำ สำหรับเครื่องเดิมอื่นให้สำรองแล้วรัน `C:\xampp\php\php.exe tools\migrate_shopping_checkout.php`

## ผลทดสอบจริง

- Regression เดิม 65 ข้อผ่าน
- BANK/COD/สลิป/สิทธิ์เดิม 51 ข้อผ่าน
- Checkout ใหม่ 46 ข้อผ่าน: ส่วนลดจำนวนเงิน เปอร์เซ็นต์/เพดาน หมวดหมู่ ส่งฟรี ซื้อครบ กลุ่มสมาชิก Flash Sale/หมดเวลา ราคาพิเศษ หลายชิ้น เงื่อนไขใช้ร่วม สิทธิ์รวม คืนสิทธิ์เมื่อยกเลิก tokenยอดปลอม snapshot CODพื้นที่/ยอด/ปิดบริการ PromptPay/CRC สลิปหลังสร้างคำสั่งซื้อ และ Webhook ที่ยังไม่ตั้งค่า
- Edge cases ราคา/สิทธิ์ 7 ข้อผ่าน: สิทธิ์ต่อสมาชิกอิสระจากสิทธิ์รวม ส่วนลดไม่เกินยอด ตัดสตางค์ไม่สูญหาย ราคาพิเศษลงเฉพาะสินค้าที่ลดจริง และเกณฑ์ส่งฟรีหลังส่วนลด
- Gateway scaffold 5 ข้อผ่านด้วย QA adapter ที่ไม่เคยลงทะเบียนในแอป: ปฏิเสธ event ไม่ลงลายเซ็น/ยอดไม่ตรง และรับ event ที่ผ่านการตรวจแบบครั้งเดียว ไม่ใช่ผลทดสอบกับ Gateway จริง
- Chrome 15 page/viewports ที่ 1440/768/390px ผ่าน: Checkout, รายละเอียด PromptPay, Admin โปรโมชั่น ขนส่ง และการตั้งค่าจ่ายเงิน พร้อมลองคูปอง/ยอดสด สร้างคำสั่งซื้อใน Browser และอ่าน QR หลังสร้างรายการกลับด้วย jsQR ซึ่งเป็น decoder คนละตัวกับ encoder
- decoder ทดสอบใช้ Apache-2.0 source commit 8e6a036beafa7053dd44b1b76ac578d22b1b3311 เก็บ license/source ไว้ และไม่โหลดให้ผู้ใช้จริง
- PHP syntax ผ่าน 98 ไฟล์ ณ รอบตรวจ
- ทดสอบบน babyproducts_test_payments_shopping_20261110 เท่านั้น ไม่สร้างคูปอง/ผู้รับจำลองในฐานจริง ลบไฟล์เข้าใช้ QA ชั่วคราวหลังตรวจเสร็จ
- เทียบสำรองฐานจริง: users, products, categories, user_addresses และค่าคอลัมน์เดิมของ orders/order_items เหมือนเดิมทั้งหมด ไม่เปลี่ยนชื่อ/ราคา/สต็อกสินค้า รหัสผ่าน หรือคำสั่งซื้อเดิม
- ฐานจริงยังมีสินค้า 24 รายการ คำสั่งซื้อเดิม 2 รายการ ไม่มีโปรโมชั่นเปิดเอง COD ยังเปิดตามเดิม BANK/PromptPay ยังปิดรอข้อมูลรับเงินจริง

ระบบยังทดสอบบน localhost ไม่ใช่การรับเงินจริงผ่านธนาคาร/Gateway และยังต้องเตรียม HTTPS ที่เชื่อถือได้ ข้อมูลบัญชีจริง และนโยบายกิจการก่อนเปิดบริการจริง
