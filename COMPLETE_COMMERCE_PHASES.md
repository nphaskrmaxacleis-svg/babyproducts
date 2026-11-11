# ระยะการพัฒนาตามคำขอฉบับสมบูรณ์

เริ่ม 10 พฤศจิกายน 2026 สำรองใน backups/complete-commerce-20261110-102527/ ก่อนแก้ไฟล์และฐานข้อมูล

| ระยะ | สถานะ |
|---|---|
| 1 ตรวจโครงสร้าง/สำรอง | เสร็จ สำรอง SQL และไฟล์เดิม ใช้ระบบเดิม ไม่สร้างร้านใหม่ |
| 2 Login/Register | ใช้ hash/verify เดิม เพิ่มคำแปล UI; regression เดิม 65 ข้อผ่าน ไม่เปลี่ยนรหัสผ่านจริง |
| 3 สต๊อก/คำสั่งซื้อ | เพิ่ม product_variants / inventory_movements ใน includes/inventory.php, admin/inventory.php, cart/checkout; ทดสอบกัน/คืนสต๊อกและสอง PDO แย่งชิ้นสุดท้ายผ่าน ต้องแบ่งสต๊อกจริงก่อนเปิดแยกรุ่น |
| 4 ที่อยู่/ปักหมุด | เพิ่มพิกัดใน user_addresses/orders, coordinates.php, location_picker.php, map_search.php, location.js; ใช้ Leaflet/OSM แบบสมัครใจ Photon ค้นแบบกด สมุดที่อยู่ 34+4 ข้อผ่าน ค้นสถานที่จริงผ่าน |
| 5 ชำระเงิน/สลิปซ้ำ | payments.php เพิ่ม SHA-256 และ slip_fingerprints บล็อกภาพซ้ำรวมข้ามสมาชิก; payment regression 51 ข้อผ่าน ต้องตรวจเงินจริงเอง |
| 6 ภาษี/เอกสาร | taxes.php, documents.php, admin/taxes.php, order_document.php; เพิ่ม tax_settings/tax_rates/order_documents พร้อม snapshot คำสั่งซื้อ ทดสอบรวม/ไม่รวม/ยกเว้น/หลังคูปองผ่าน ไม่เปิด VAT แทนร้าน |
| 7 คูปอง | ใช้ตารางเดิม เพิ่ม coupons.php ตรวจร่วมค่าขนส่งและภาษี; shopping 46 และ edge 7 ข้อผ่าน |
| 8 ขนส่ง/แผนข้อตกลง | shipping.php, shipping_agreements.php, order_detail.php; เพิ่ม COD fee, ETA, น้ำหนัก/ขนาด/วันที่จัดส่ง และ shipping_agreements ไม่ใส่สัญญาหรือราคาสมมติ ต้องกรอกข้อเสนอจริง |
| 9 TH/EN | i18n.php, language.php, lang/*.php, header/footer และหน้าลูกค้าหลัก; เก็บภาษาใน Session มีช่องชื่อ/รายละเอียดอังกฤษจาก Admin fallback ต้นฉบับ ข้อความบริหารบางส่วนยังเป็นไทย |
| 10 ทดสอบรวม | รวม 332 ข้อจากชุดระบบ/ฐานข้อมูล/Chrome/เทียบข้อมูลเดิม รายละเอียดและไฟล์รายระยะใน COMPLETE_COMMERCE_REPORT.md |
