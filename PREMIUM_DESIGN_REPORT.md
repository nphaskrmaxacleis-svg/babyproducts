# รายงานปรับดีไซน์ Modern Premium Baby Boutique — 9 พฤศจิกายน 2026

ต่อยอดโครงการ PHP/PDO เดิม ไม่สร้างเว็บไซต์ใหม่ เปลี่ยนสไตล์หน้าแรก รายการสินค้า รายละเอียด ตะกร้า เข้าสู่ระบบ และสมัครสมาชิกให้สอดคล้องกัน ระบบสมาชิก Admin สั่งซื้อ และสิทธิ์เข้าถึงยังอยู่ครบ

## ดีไซน์

- ใช้ Warm Ivory #FAF7F2, Soft Cream #F3E9DF, Dusty Rose #B65B78, Muted Beige #D8C5B4, Charcoal #303642 และ White #FFFFFF
- ฟอนต์ Prompt พร้อม Tahoma สำรอง หัวข้อชัดเจน ปุ่มรูปทรงเรียบ ขอบโค้งพอดี เงาเบา รูปสินค้าใหญ่สัดส่วน 1:1 และ object-fit: contain
- เปลี่ยน Hero เป็นภาพห้องเด็กแสงธรรมชาติ เสื้อผ้าพับ ตุ๊กตาผ้าจริง ผ้าห่มครีม และขวดไม่มีแบรนด์ คงข้อความ “โลกใบเล็ก ๆ ที่เต็มไปด้วยความรัก”
- ถอดวงโคจร ดาว หัวใจ สติกเกอร์ ภาพลอย และป้ายภาษาอังกฤษส่วนเกินออกจากหน้าแรก ไม่เล่นแบนเนอร์อัตโนมัติ
- รักษาแท็บสินค้า ดูแบบย่อ เพิ่มตะกร้าทันที เมนูติดด้านบน และ reduced-motion สำหรับผู้ลดการเคลื่อนไหว
- ตะกร้าบนมือถือปรับเป็นรายการที่อ่านง่าย ไม่ต้องเลื่อนตารางแนวนอน

## ภาพใหม่ที่มีไฟล์จริงแล้ว

สร้างด้วย built-in imagegen: Hero ใหม่ 1 ภาพ และแก้ฉากหลังภาพสินค้าจากต้นฉบับ 16 ภาพ ภาพทั้งหมดเป็น AI สำหรับสินค้าตัวอย่าง ไม่ใช่ภาพถ่ายจากผู้ผลิต เก็บไฟล์เดิมไว้ครบ

Hero: assets/images/boutique-nursery-hero-ai-v1.webp พร้อมขนาด 960 และ 640 พิกเซล

| SKU | สินค้า | ฉากหลังใหม่ | ไฟล์หลัก | KB |
|---|---|---|---|---:|
| BP1 | บอดี้สูทผ้าฝ้าย | ลินินครีม | uploads/products/bp1-studio-ai-premium-v2.webp | 145 |
| BP10 | รถเข็นพับได้ | ห้องเด็กมินิมอล | uploads/products/bp10-studio-ai-premium-v2.webp | 106.1 |
| BP11 | รถเข็นรุ่นเดินทาง | ห้องเด็กมินิมอล | uploads/products/bp11-studio-ai-premium-v2.webp | 102.3 |
| BP12 | รถเข็นสีครีม | ห้องเด็กมินิมอล | uploads/products/bp12-studio-ai-premium-v2.webp | 101.3 |
| BP16 | แปรงอาบน้ำเด็ก | ไม้ธรรมชาติ | uploads/products/bp16-studio-ai-premium-v2.webp | 109.7 |
| BP17 | ผ้าขนหนูเด็ก | ไม้ธรรมชาติ | uploads/products/bp17-studio-ai-premium-v2.webp | 167.6 |
| BP18 | ชุดหวีแปรงเด็ก | ไม้ธรรมชาติ | uploads/products/bp18-studio-ai-premium-v2.webp | 123.7 |
| BP2 | ชุดนอนลายหมี | ลินินครีม | uploads/products/bp2-studio-ai-premium-v2.webp | 135.2 |
| BP3 | หมวกเด็กสีครีม | ลินินครีม | uploads/products/bp3-studio-ai-premium-v2.webp | 233.2 |
| BP7 | ตุ๊กตาหมีนุ่ม | สตูดิโอชมพูตุ่น | uploads/products/bp7-studio-ai-premium-v2.webp | 148.9 |
| BP8 | บล็อกไม้สีพาสเทล | สตูดิโอชมพูตุ่น | uploads/products/bp8-studio-ai-premium-v2.webp | 74.1 |
| BP9 | ของเล่นเขย่ามือ | สตูดิโอชมพูตุ่น | uploads/products/bp9-studio-ai-premium-v2.webp | 44.9 |
| DEMO-019 | ชุดเสื้อกางเกงเด็ก ลายเมฆ | ลินินครีม | uploads/products/demo-019-studio-ai-premium-v2.webp | 174.3 |
| DEMO-021 | หนังสือผ้า ลายสัตว์น้อย | สตูดิโอชมพูตุ่น | uploads/products/demo-021-studio-ai-premium-v2.webp | 141.1 |
| DEMO-022 | รถเข็นเด็ก รุ่นพับเก็บ สีฟ้าพาสเทล | ห้องเด็กมินิมอล | uploads/products/demo-022-studio-ai-premium-v2.webp | 93.3 |
| DEMO-024 | ชุดผ้าเช็ดหน้าเด็ก สีพาสเทล | ไม้ธรรมชาติ | uploads/products/demo-024-studio-ai-premium-v2.webp | 263.8 |

คงภาพเดิมที่เป็น photorealistic อยู่แล้วอีก 8 รายการในหมวดอาหารและผ้าอ้อม เพราะไม่จำเป็นต้องเปลี่ยนประเภทหรือรายละเอียดสินค้า ภาพสินค้าใหม่มีขนาดหลัก 1200×1200 และขนาดย่อ 800/480 พิกเซล

ภาพที่ยังต้องสร้างสำหรับขอบเขตงานนี้: ไม่มี ภาพถ่ายจากสินค้าจริงของร้านยังไม่ได้รับ; อาหารตัวอย่าง A/B/C ยังคงเป็นแนวคิดสินค้าเนื่องจากรายละเอียดจริงยังไม่ระบุ

## ไฟล์ที่แก้หรือเพิ่ม

- index.php และ includes/boutique_home.php: ต่อเลย์เอาต์ใหม่เข้ากับ query สินค้าเดิม
- includes/header.php และ includes/footer.php: wordmark เมนู footer และโหลด CSS แบบมี version
- assets/css/premium.css: ดีไซน์ร่วมทุกหน้าและ responsive 3 ช่วงหลัก
- assets/js/script.js: เมนู active ตามหน้า/ตัวกรอง และลดหัวข้ออังกฤษใน quick view
- includes/functions.php: ป้ายการ์ดภาษาไทย
- products.php, product_detail.php, cart.php, login.php, register.php: ข้อความและองค์ประกอบที่สอดคล้องกัน
- database.sql และ tools/seed_extra_products.php: เส้นทางภาพสำหรับติดตั้งใหม่ ไม่มีการนำเข้า SQL ซ้ำ
- tools/premium-image-manifest.json: prompt และแหล่งภาพก่อน/หลังของแต่ละ SKU กับ Hero
- tools/premium_boutique_assets.php: สำรองข้อมูล แปลงภาพ เปลี่ยนเฉพาะ image และตรวจข้อมูลเดิม
- tests/boutique_browser.ps1: CDP browser checks และ screenshots
- tests/integration.php: ยืนยันหน้า profile จาก h1 เพื่อไม่สับสนกับลิงก์บัญชีใน footer
- README.md, TEST_REPORT.md และรายงานนี้

## ผลทดสอบ

- PHP syntax ผ่านสำหรับไฟล์ที่แก้
- Chrome DevTools emulation ที่ 1440×1100, 768×1100 และ 390×844 ผ่านครบ 6 หน้า × 3 ขนาด = 18 กรณี ตรวจ viewport จริง ไม่ใช้ screenshot ที่ตัดจากหน้าต่างใหญ่ ภาพมองเห็นโหลดสำเร็จ ไม่มี horizontal overflow มีเมนูและหัวข้อ และไม่มีองค์ประกอบตกแต่งเก่า
- แท็บสินค้า ดูแบบย่อ และ AJAX cart ผ่านการคลิกใน browser
- Integration ผ่าน 64 ข้อบนฐานข้อมูลใหม่แยก babyproducts_test_boutique_20261109 ครอบคลุมสมาชิก สิทธิ์ Admin รหัสผ่าน ตะกร้า COD สต็อก การยกเลิก ยืนยันเงิน รายงาน และการอัปโหลด
- ตรวจเทียบ snapshot สินค้า 24 แถว: เปลี่ยนเฉพาะ image สำหรับ 16 แถว ชื่อ ราคา สต็อก active และข้อมูลอื่นไม่เปลี่ยน ไม่มีการลบข้อมูลหรือตาราง
- ภาพหน้าจอทั้ง 18 กรณีอยู่ในโฟลเดอร์สำรอง/screenshots/ ยังไม่ได้ทดสอบบนโทรศัพท์จริง

## สำรองและตรวจผล

สำรองไว้ที่ C:\xampp\htdocs\babyproducts\backups\premium-boutique-20261109-091230 รวมไฟล์ PHP/CSS/JS เดิม, products.sql, products-before.json, ภาพสินค้าเดิม, generated-sources/ และ contact sheet เปรียบเทียบก่อน–หลัง โฟลเดอร์นี้ถูก Apache ปิดการเข้าถึง

เปิด http://localhost/babyproducts/?v=premium แล้วกด Ctrl+Shift+R ตรวจหน้ารายการสินค้า รายละเอียด ตะกร้า และเข้าสู่ระบบด้วยบัญชีเดิม ไม่ต้องนำเข้าฐานข้อมูลหรือสร้าง Admin ใหม่

รัน `php tools/premium_boutique_assets.php verify` เพื่อตรวจภาพและข้อมูลสินค้าเทียบ snapshot ได้ ใช้ภาพแนวคิด AI โดยกำกับที่ Hero การ์ดสินค้า รายละเอียด ตะกร้า และ footer
