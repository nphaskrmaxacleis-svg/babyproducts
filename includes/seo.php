<?php
declare(strict_types=1);
$seoPage=basename($_SERVER['SCRIPT_NAME']??'index.php');
$seoPages=[
    'index.php'=>['สินค้าแม่และเด็ก คัดสรรเพื่อครอบครัว','เลือกดูเสื้อผ้าเด็ก ของเล่น อุปกรณ์ให้อาหาร รถเข็น ผ้าอ้อม และผลิตภัณฑ์ดูแลเด็ก พร้อมข้อมูลสินค้าและระบบสั่งซื้อเก็บเงินปลายทาง'],
    'products.php'=>['สินค้าทั้งหมดและหมวดหมู่','ค้นหาสินค้าแม่และเด็ก เลือกหมวดหมู่ กรองราคา และดูรายละเอียดสินค้าก่อนเพิ่มลงตะกร้า'],
    'product_detail.php'=>['รายละเอียดสินค้า','ดูรายละเอียด ราคา และจำนวนสินค้าคงเหลือ พร้อมเลือกจำนวนและเพิ่มสินค้าในตะกร้า'],
    'contact.php'=>['ติดต่อเรา','ติดต่อ Baby Products ทางโทรศัพท์ อีเมล หรือส่งคำถามผ่านแบบฟอร์มภายในเว็บไซต์ พร้อมข้อมูลเวลาทำการของร้าน'],
    'privacy.php'=>['นโยบายความเป็นส่วนตัว','อ่านข้อมูลที่เว็บไซต์เก็บ วัตถุประสงค์การใช้ข้อมูล คุกกี้ Session ความยินยอม และวิธีส่งคำขอเกี่ยวกับข้อมูลส่วนบุคคล'],
    'terms.php'=>['เงื่อนไขบริการ การสั่งซื้อ และการจัดส่ง','ศึกษาวิธีสั่งซื้อสินค้า การชำระเงินปลายทาง ค่าจัดส่ง และเงื่อนไขของเว็บไซต์สาธิต Baby Products'],
    'login.php'=>['เข้าสู่ระบบสมาชิก','เข้าสู่บัญชี Baby Products เพื่อจัดการข้อมูลส่วนตัว ตำแหน่งจัดส่ง และติดตามคำสั่งซื้อ'],
    'register.php'=>['สมัครสมาชิก','สร้างบัญชี Baby Products เพื่อสั่งซื้อสินค้าแม่และเด็กและติดตามรายการสั่งซื้อของคุณ'],
    'notifications.php'=>['การแจ้งเตือน','ติดตามการแจ้งเตือนคำสั่งซื้อ การชำระเงิน และการจัดส่งของบัญชีคุณ'],
    'cart.php'=>['ตะกร้าสินค้า','ตรวจสอบรายการ จำนวน และยอดรวมสินค้าในตะกร้าก่อนดำเนินการสั่งซื้อ'],
    'checkout.php'=>['ยืนยันคำสั่งซื้อ','ตรวจสอบข้อมูลจัดส่ง เลือกเก็บเงินปลายทางหรือโอนเงิน และแนบหลักฐานเพื่อให้ร้านตรวจสอบการชำระเงิน'],
    'addresses.php'=>['ที่อยู่ของฉัน','จัดการที่อยู่จัดส่งที่บันทึกไว้ เพิ่ม แก้ไข ลบ และเลือกที่อยู่เริ่มต้นสำหรับคำสั่งซื้อครั้งต่อไป'],
    'profile.php'=>['บัญชีของฉัน','จัดการข้อมูลส่วนตัว ลิงก์แผนที่จัดส่ง รหัสผ่าน และความยินยอมของบัญชีสมาชิก'],
    'my_orders.php'=>['คำสั่งซื้อของฉัน','ตรวจสอบประวัติคำสั่งซื้อและติดตามสถานะการจัดส่งในบัญชีของคุณ'],
    'order_detail.php'=>['รายละเอียดคำสั่งซื้อ','ตรวจสอบรายการสินค้า ยอดรวม ข้อมูลจัดส่ง และสถานะของคำสั่งซื้อของคุณ'],
    'privacy_request.php'=>['คำขอข้อมูลส่วนบุคคล','ส่งคำขอเข้าถึงหรือขอลบข้อมูลส่วนบุคคลให้ผู้ดูแลร้านค้าตรวจสอบและดำเนินการ'],
    '404.php'=>['ไม่พบหน้าที่ต้องการ','ไม่พบหน้าที่คุณเปิด สามารถกลับหน้าหลัก ค้นหาสินค้า หรือติดต่อ Baby Products เพื่อขอความช่วยเหลือ'],
];
$seoEntry=$seoPages[$seoPage]??['Baby Products','เว็บไซต์สินค้าแม่และเด็ก Baby Products'];
$seoTitle=$seoEntry[0].' | Baby Products';$seoDescription=$seoEntry[1];
$seoAdmin=str_contains(str_replace('\\','/',$_SERVER['SCRIPT_NAME']??''),'/admin/');
if($seoAdmin){$seoTitle=($title??'จัดการร้านค้า').' | Baby Products';$seoDescription='ระบบจัดการร้านค้า Baby Products สำหรับผู้ดูแลที่ได้รับสิทธิ์';}
elseif($seoPage==='product_detail.php'&&!empty($p['name'])){
    $seoTitle=$p['name'].' | Baby Products';
    $seoDescription=mb_substr(preg_replace('/\s+/u',' ',strip_tags($p['description']??'')),0,125).' ดูราคาและรายละเอียดสินค้าได้ที่ Baby Products';
}
elseif($seoPage==='products.php'){
    $query=is_string($_GET['q']??null)?trim($_GET['q']):'';
    if($query!=='')$seoTitle='ผลค้นหา '.mb_substr($query,0,60).' | Baby Products';
    elseif(isset($_GET['featured']))$seoTitle='สินค้าแนะนำ | Baby Products';
    if((int)($_GET['page']??1)>1)$seoTitle='หน้า '.(int)$_GET['page'].' · '.$seoTitle;
}
if(current_language()==='en'){$seoTitle=($seoPage==='product_detail.php'&&!empty($p)?localized_name($p):t($seoEntry[0])).' | Baby Products';$seoDescription=$seoPage==='product_detail.php'&&!empty($p)?mb_substr(strip_tags(localized_description($p)),0,155):'Thoughtfully selected essentials for parents and babies. Browse products, manage your account and follow your orders at Baby Products.';}
$seoNoindex=$seoAdmin||!in_array($seoPage,['index.php','products.php','product_detail.php','contact.php','privacy.php','terms.php'],true)||http_response_code()===404;
