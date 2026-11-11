อัปเดตล่าสุด: มีดีไซน์ Modern Premium และภาพเปลี่ยนฉากหลังรุ่นใหม่ 16 รายการ ดู PREMIUM_DESIGN_REPORT.md สำหรับเส้นทางภาพและผลทดสอบล่าสุด รายงานด้านล่างเป็นประวัติชุดภาพก่อนปรับดีไซน์

# รายงานภาพสินค้า Baby Products — 9 พฤศจิกายน 2026

สร้างภาพแนวถ่ายสตูดิโอด้วย imagegen แบบ built-in ครบ 24 รายการ ภาพทั้งหมดเป็นภาพ AI สำหรับสินค้าจำลอง ไม่ใช่ภาพถ่ายสินค้าที่มีจำหน่ายจริง ไม่ใช้ภาพจากร้านอื่น ไม่มีโลโก้ ลายน้ำ หรือตัวหนังสือในภาพ

ไฟล์หลัก WebP 1200 × 1200 พร้อมขนาด 800 และ 480 พิกเซล รวม 72 ไฟล์ อยู่ใน uploads/products/ ชื่อไฟล์ต่างกันทุก SKU ตรวจ SHA-256 ว่าภาพหลักไม่ซ้ำกัน และใช้ object-fit: contain

สำรองก่อนแก้ที่ backups/product-images-20261109-084208/ ได้แก่ products.sql, products-before.json, ไฟล์ PHP/CSS/JS ที่เกี่ยวข้อง, ภาพหมวดหมู่เดิม และ generated-sources/ สำหรับ PNG ต้นฉบับ 24 ไฟล์ โฟลเดอร์ backups ปิดการเข้าถึงผ่าน Apache ด้วย .htaccess

## รายการภาพที่สร้างสำเร็จ

| SKU | สินค้า | ไฟล์หลัก | KB | หน้าร้าน |
|---|---|---|---:|---|
| BP1 | บอดี้สูทผ้าฝ้าย | uploads/products/bp1-studio-ai-v1.webp | 49.1 | เปิด |
| BP2 | ชุดนอนลายหมี | uploads/products/bp2-studio-ai-v1.webp | 57.1 | เปิด |
| BP3 | หมวกเด็กสีครีม | uploads/products/bp3-studio-ai-v1.webp | 127.6 | เปิด |
| BP4 | อาหารเด็กตัวอย่าง A | uploads/products/bp4-studio-ai-v1.webp | 38.5 | ปิดใช้งาน |
| BP5 | อาหารเด็กตัวอย่าง B | uploads/products/bp5-studio-ai-v1.webp | 37.3 | เปิด |
| BP6 | อาหารเด็กตัวอย่าง C | uploads/products/bp6-studio-ai-v1.webp | 31.1 | เปิด |
| BP7 | ตุ๊กตาหมีนุ่ม | uploads/products/bp7-studio-ai-v1.webp | 134 | เปิด |
| BP8 | บล็อกไม้สีพาสเทล | uploads/products/bp8-studio-ai-v1.webp | 93.4 | เปิด |
| BP9 | ของเล่นเขย่ามือ | uploads/products/bp9-studio-ai-v1.webp | 46 | เปิด |
| BP10 | รถเข็นพับได้ | uploads/products/bp10-studio-ai-v1.webp | 91.5 | เปิด |
| BP11 | รถเข็นรุ่นเดินทาง | uploads/products/bp11-studio-ai-v1.webp | 82.1 | เปิด |
| BP12 | รถเข็นสีครีม | uploads/products/bp12-studio-ai-v1.webp | 82.6 | เปิด |
| BP13 | ผ้าอ้อมกลางวัน | uploads/products/bp13-studio-ai-v1.webp | 38.5 | เปิด |
| BP14 | ผ้าอ้อมกลางคืน | uploads/products/bp14-studio-ai-v1.webp | 39 | เปิด |
| BP15 | ผ้าอ้อมแบบผ้า | uploads/products/bp15-studio-ai-v1.webp | 208.8 | เปิด |
| BP16 | แปรงอาบน้ำเด็ก | uploads/products/bp16-studio-ai-v1.webp | 48 | เปิด |
| BP17 | ผ้าขนหนูเด็ก | uploads/products/bp17-studio-ai-v1.webp | 114.2 | เปิด |
| BP18 | ชุดหวีแปรงเด็ก | uploads/products/bp18-studio-ai-v1.webp | 88 | เปิด |
| DEMO-019 | ชุดเสื้อกางเกงเด็ก ลายเมฆ | uploads/products/demo-019-studio-ai-v1.webp | 53.6 | เปิด |
| DEMO-020 | ข้าวบดฟักทอง บรรจุถ้วย | uploads/products/demo-020-studio-ai-v1.webp | 83.9 | เปิด |
| DEMO-021 | หนังสือผ้า ลายสัตว์น้อย | uploads/products/demo-021-studio-ai-v1.webp | 164.4 | เปิด |
| DEMO-022 | รถเข็นเด็ก รุ่นพับเก็บ สีฟ้าพาสเทล | uploads/products/demo-022-studio-ai-v1.webp | 82.8 | เปิด |
| DEMO-023 | ผ้าอ้อมสำเร็จรูป ไซซ์ M แพ็กทดลอง | uploads/products/demo-023-studio-ai-v1.webp | 39 | เปิด |
| DEMO-024 | ชุดผ้าเช็ดหน้าเด็ก สีพาสเทล | uploads/products/demo-024-studio-ai-v1.webp | 189.3 | เปิด |

## ภาพหรือรายละเอียดที่ยังขาด

ไม่มีภาพ AI ที่ยังขาดจากรายการตัวอย่าง 24 SKU แต่ยังไม่มีภาพถ่ายจากสินค้าจริงหรือผู้ผลิต สินค้าอาหารเด็ก A/B/C มีเพียงชื่อทั่วไป จึงใช้ภาพแนวคิดบรรจุภัณฑ์เปล่าที่แตกต่างกัน ไม่ระบุส่วนผสม อายุ หรือคุณประโยชน์ ต้องยืนยันบรรจุภัณฑ์กับสินค้าจริงก่อนใช้ขายจริง ภาพ AI ถูกกำกับชัดเจนในการ์ด รายละเอียด ตะกร้า และหน้าดูแบบย่อ

BP4 ถูกปิดใช้งานผ่านหลังร้านหลังนำภาพเข้า (พบ POST /admin/products.php ใน access log เวลา 09:01:11) คงสถานะล่าสุดไว้ ภาพสร้างครบแต่หน้า detail ของรายการที่ปิดตอบ 404 ตามสิทธิ์การแสดงสินค้า ไม่มีการเปิดใช้งานกลับอัตโนมัติ

## ไฟล์ที่แก้และเพิ่ม

- includes/product_images.php: เลือกภาพ/ภาพสำรอง srcset ป้ายกำกับภาพ AI แปลงและตรวจภาพอัปโหลด
- index.php: แบนเนอร์และภาพตัวแทนหมวดหมู่ใช้ภาพสินค้าที่สร้างใหม่
- database.sql และ tools/seed_extra_products.php: เส้นทางภาพสำหรับติดตั้งใหม่เท่านั้น ไม่รันซ้ำในฐานข้อมูลหลัก
- includes/functions.php: การ์ดและข้อมูล modal ใช้ภาพเฉพาะรายการ
- product_detail.php และ cart.php: ภาพพร้อมคำกำกับและขนาดที่เหมาะสม
- admin/bootstrap.php และ admin/product_edit.php: อัปโหลด WebP ภาพย่อ ภาพตัวอย่าง และปุ่มเปลี่ยนเฉพาะภาพ
- assets/css/storefront.css และ assets/js/script.js: contain ภาพทุกขนาด preview และกรณีโหลดภาพไม่ได้
- assets/images/product-unavailable.svg: ภาพสำรองเมื่อไม่มีหมวดหมู่ที่ตรง
- tools/product-image-manifest.json: prompt แยกทุก SKU และแหล่งต้นฉบับ เก็บใน tools ที่ Apache ปิดไว้
- tools/product_photo_pipeline.php และ tools/product_photo_report.php: สำรอง แปลง ตรวจ นำเข้า และออกรายงาน
- tests/product_image_helpers.php และ tests/integration.php: ทดสอบรูปและการเปลี่ยนเฉพาะภาพ
- README.md และ TEST_REPORT.md: คู่มือและผลตรวจล่าสุด

## ผลตรวจ

- PHP helpers ผ่าน 6 ข้อ: fallback, เส้นทางผิด, ป้าย AI, ขนาด WebP, contain ไม่ยืดรูป และปฏิเสธไฟล์ที่ไม่ใช่ภาพ
- Integration ผ่าน 64 ข้อ ในฐานข้อมูลแยก babyproducts_test_photos_20261109 รวมอัปโหลดภาพ แปลง WebP/ภาพย่อ เปลี่ยนเฉพาะภาพโดยไม่แก้ข้อมูลอื่น และปฏิเสธไฟล์ PHP ปลอมเป็นภาพ
- Chrome headless ผ่าน 82 จุดทั้ง 1440 และ 500 พิกเซล: ตรวจ HTTP/เนื้อหาไฟล์ภาพครบ 24 SKU หน้ารายการทั้งสองหน้า รายละเอียดตามสถานะสินค้า ตะกร้า modal ภาพสำรอง และการล้นแนวนอน
- ตรวจภาพจริงของหน้าแรก หน้ารายการ รายละเอียดจอแคบ และตะกร้า เก็บภาพหน้าจอในโฟลเดอร์สำรอง/screenshots ยังไม่ได้ตรวจบนโทรศัพท์จริง
- ตรวจโฟลเดอร์สำรองและ tools ผ่าน Apache ตอบ 403 ไม่มีการเปิดเผย SQL หรือ manifest ต่อสาธารณะ

## วิธีตรวจ

1. เปิด http://localhost/babyproducts/ แล้วกด Ctrl+Shift+R เลื่อนถึงการ์ดสินค้า
2. เปิด products.php ตรวจทั้งสองหน้า และกดดูรายละเอียด/ดูแบบรวดเร็ว
3. เพิ่มสินค้าลงตะกร้า แล้วเปิด cart.php เพื่อตรวจภาพตรง SKU
4. Admin > สินค้า > แก้ไข เลื่อนถึงรูปปัจจุบันและปุ่มเปลี่ยนเฉพาะรูปภาพ ไฟล์รับ JPEG/PNG/WebP ไม่เกิน 5 MB และ 4096 × 4096 แปลงเป็น WebP จัตุรัสโดยรักษาสัดส่วน ไม่ยืดหรือตัดสินค้า
5. รัน php tools/product_photo_pipeline.php verify เพื่อตรวจภาพหลัก/ภาพย่อและเทียบข้อมูลกับ backup

Prompt แยกแต่ละ SKU และ master prompt อยู่ที่ tools/product-image-manifest.json ใช้ built-in imagegen เท่านั้น ไม่ได้ใช้ CLI/API fallback
