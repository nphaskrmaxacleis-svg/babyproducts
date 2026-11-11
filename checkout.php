<?php
require 'includes/functions.php';$u=auth();$error='';$bank=bank_settings();$stored=null;
$_SESSION['checkout_token']??=bin2hex(random_bytes(32));
// Return an already-created order even if its saved-address card was later deleted.
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$duplicateOrder=q('SELECT id FROM orders WHERE token=? AND user_id=?',[post('token'),$u['id']])->fetchColumn();if($duplicateOrder)go('order_detail.php?id='.$duplicateOrder);}
$savedAddresses=member_addresses((int)$u['id']);
$selectedAddress=$_SERVER['REQUEST_METHOD']==='POST'?(int)post('saved_address_id'):(int)($_GET['address_id']??($savedAddresses&&$savedAddresses[0]['is_default']?$savedAddresses[0]['id']:0));
$chosenAddress=null;foreach($savedAddresses as $candidate)if((int)$candidate['id']===$selectedAddress)$chosenAddress=$candidate;
if($selectedAddress&&!$chosenAddress){http_response_code(404);require 'includes/header.php';echo '<h1>ไม่พบที่อยู่ของคุณ</h1>';require 'includes/footer.php';exit;}
$shippingValues=['recipient'=>$u['name'],'phone'=>$u['phone'],'address'=>$u['address']??'','province'=>'','district'=>'','subdistrict'=>'','postcode'=>'','map_url'=>'','address_note'=>'','latitude'=>'','longitude'=>''];
if($chosenAddress){foreach(['recipient','phone','province','district','subdistrict','postcode','map_url','latitude','longitude'] as $key)$shippingValues[$key]=$chosenAddress[$key];$shippingValues['address']=$chosenAddress['address_line'];$shippingValues['address_note']=$chosenAddress['details'];}
if($_SERVER['REQUEST_METHOD']==='POST')foreach($shippingValues as $key=>$value)$shippingValues[$key]=post($key);
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
    $token=post('token');$existing=q('SELECT id FROM orders WHERE token=? AND user_id=?',[$token,$u['id']])->fetchColumn();if($existing)go('order_detail.php?id='.$existing);
    if(!hash_equals($_SESSION['checkout_token'],$token))throw new RuntimeException('รายการนี้หมดอายุ กรุณาลองใหม่');
    foreach(['recipient','phone','address','province','district','subdistrict','postcode'] as $field)if(post($field)===''||mb_strlen(post($field))>($field==='address'?2000:120))throw new RuntimeException('กรุณากรอกข้อมูลจัดส่งให้ครบถ้วน');
    if(!preg_match('/^[0-9]{9,10}$/D',post('phone'))||!preg_match('/^[0-9]{5}$/D',post('postcode'))||mb_strlen(post('note'))>2000)throw new RuntimeException('เบอร์โทรศัพท์ รหัสไปรษณีย์ หรือหมายเหตุไม่ถูกต้อง');
    if(mb_strlen(post('address_note'))>1000||mb_strlen(post('map_url'))>2000||(post('map_url')!==''&&!valid_delivery_map(post('map_url'))))throw new RuntimeException('รายละเอียดที่อยู่หรือลิงก์แผนที่ไม่ถูกต้อง');
    $coordinates=shipping_coordinates($_POST);
    $method=post('payment_method')?:'COD';if(!in_array($method,['COD','BANK','PROMPTPAY'],true))throw new RuntimeException('ช่องทางชำระเงินไม่ถูกต้อง');
    if($method==='BANK'&&!bank_available($bank))throw new RuntimeException('ร้านค้ายังไม่ได้เปิดรับโอนเงิน กรุณาเลือกเก็บเงินปลายทาง');
    if($method==='PROMPTPAY'&&!promptpay_available($bank))throw new RuntimeException('ร้านค้ายังไม่ได้เปิด PromptPay');
    if(in_array($method,['BANK','PROMPTPAY'],true)&&((($_FILES['slip']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)||post('attach_now')==='1'))$stored=store_payment_slip($_FILES['slip']??[]);
    $cart=$_SESSION['cart']??[];if(!$cart)throw new RuntimeException('ไม่มีสินค้าในตะกร้า');ksort($cart);
    db()->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');db()->beginTransaction();address_lock_member((int)$u['id']);
    if($selectedAddress&&!q('SELECT id FROM user_addresses WHERE id=? AND user_id=? FOR UPDATE',[$selectedAddress,$u['id']])->fetchColumn())throw new RuntimeException('ที่อยู่ที่เลือกถูกลบ กรุณาเลือกใหม่');
    if(post('save_address')==='1'){
        $savedValues=validate_shipping_address(['label'=>$chosenAddress['label']??'','recipient'=>post('recipient'),'phone'=>post('phone'),'address_line'=>post('address'),'subdistrict'=>post('subdistrict'),'district'=>post('district'),'province'=>post('province'),'postcode'=>post('postcode'),'details'=>post('address_note'),'map_url'=>post('map_url'),'latitude'=>post('latitude'),'longitude'=>post('longitude')]);
        save_member_address((int)$u['id'],$savedValues,post('save_as_default')==='1');
    }
    $bank=q('SELECT * FROM payment_settings WHERE id=1 FOR UPDATE')->fetch();if($method==='BANK'&&!bank_available($bank))throw new RuntimeException('ช่องทางโอนเงินถูกปิด กรุณาเปิดหน้าใหม่');if($method==='PROMPTPAY'&&!promptpay_available($bank))throw new RuntimeException('PromptPay ถูกปิด กรุณาเปิดหน้าใหม่');
    $rows=[];$quoteRows=[];$subtotal=0;
    foreach($cart as $cartKey=>$quantity){[$id,$variantId]=cart_identity($cartKey);
        $product=q('SELECT p.* FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1 AND c.active=1 FOR UPDATE',[$id])->fetch();
        if(!$product||!is_int($quantity)||$quantity<1||$product['stock']<$quantity)throw new RuntimeException('สินค้าไม่พร้อมจำหน่ายหรือสต็อกไม่เพียงพอ กรุณาตรวจสอบตะกร้า');
        $product['cart_key']=$cartKey;$product['variant_id']=$variantId?:null;if($product['variants_enabled']){$variant=q('SELECT * FROM product_variants WHERE id=? AND product_id=? AND active=1 FOR UPDATE',[$variantId,$id])->fetch();if(!$variant||$variant['stock']<$quantity)throw new RuntimeException('สี/ไซซ์ที่เลือกมีสต๊อกไม่เพียงพอ');$product['variant_color']=$variant['color'];$product['variant_size']=$variant['size'];}elseif($variantId)throw new RuntimeException('รุ่นสินค้าไม่ถูกต้อง');
        $subtotal+=cents($product['price'])*$quantity;$rows[]=[$product,$quantity];$product['quantity']=$quantity;$quoteRows[]=$product;
    }
    $serviceId=(int)post('shipping_service_id')?:1;
    $quote=commerce_quote($quoteRows,(int)$u['id'],post('province'),$serviceId,post('coupon_codes'),post('skip_auto')==='1',true,$method);
    if(post('quote_required')==='1'&&!hash_equals(commerce_quote_token($quote,(int)$u['id']),post('quote_token')))throw new RuntimeException('ราคาหรือสิทธิ์ส่วนลดเปลี่ยน กรุณาตรวจยอดใหม่ก่อนยืนยัน');
    if($method==='COD')commerce_check_cod($bank,$quote,post('province'));
    if($method==='PROMPTPAY'&&$quote['total']<1)throw new RuntimeException('ยอดศูนย์ไม่ต้องโอน กรุณาเลือกช่องทางอื่น');
    $shipping=$quote['shipping'];
    q('INSERT INTO orders(user_id,token,recipient,phone,address,province,district,subdistrict,postcode,note,subtotal,shipping,total,payment_method,bank_name,bank_account_name,bank_account_number,map_url,address_note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$u['id'],$token,post('recipient'),post('phone'),post('address'),post('province'),post('district'),post('subdistrict'),post('postcode'),post('note'),$subtotal/100,$shipping/100,$quote['total']/100,$method,$method==='BANK'?$bank['bank_name']:null,$method==='BANK'?$bank['account_name']:null,$method==='BANK'?$bank['account_number']:null,post('map_url')!==''?post('map_url'):null,post('address_note')!==''?post('address_note'):null]);
    $orderId=(int)db()->lastInsertId();
    q('UPDATE orders SET product_discount=?,coupon_discount=?,shipping_base=?,shipping_discount=?,shipping_service_id=?,shipping_service_name=?,price_breakdown=?,promptpay_type=?,promptpay_recipient=?,promptpay_name=? WHERE id=?',[$quote['product_discount']/100,$quote['coupon_discount']/100,$quote['shipping_base']/100,$quote['shipping_discount']/100,$quote['service']['id'],$quote['service']['name'],json_encode($quote['applied'],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$method==='PROMPTPAY'?$bank['promptpay_type']:null,$method==='PROMPTPAY'?$bank['promptpay_recipient']:null,$method==='PROMPTPAY'?$bank['promptpay_name']:null,$orderId]);
    q('UPDATE orders SET latitude=?,longitude=? WHERE id=?',[$coordinates['latitude'],$coordinates['longitude'],$orderId]);
    q('UPDATE orders SET net_before_tax=?,vat_amount=?,tax_snapshot=?,cod_fee=? WHERE id=?',[$quote['net_before_tax']/100,$quote['vat_amount']/100,json_encode($quote['tax_snapshot'],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$quote['cod_fee']/100,$orderId]);
    commerce_record_redemptions($orderId,(int)$u['id'],$quote);
    foreach($rows as [$product,$quantity]){q('INSERT INTO order_items(order_id,product_id,name,price,quantity) VALUES (?,?,?,?,?)',[$orderId,$product['id'],$product['name'],$product['price'],$quantity]);$lineId=(int)db()->lastInsertId();q('UPDATE order_items SET line_subtotal=?,discount_amount=?,line_total=? WHERE id=?',[$quote['line_gross'][$product['cart_key']]/100,($quote['line_gross'][$product['cart_key']]-$quote['line_totals'][$product['cart_key']])/100,$quote['line_totals'][$product['cart_key']]/100,$lineId]);q('UPDATE order_items SET variant_id=?,variant_color=?,variant_size=?,name_en=? WHERE id=?',[$product['variant_id'],$product['variant_color']??null,$product['variant_size']??null,$product['name_en']??null,$lineId]);inventory_reserve($product,$quantity,$orderId,(int)$u['id']);}
    $order=locked_order($orderId);order_event($order,(int)$u['id'],'created','สร้างคำสั่งซื้อ · '.payment_method_label($order));
    order_notify((int)$u['id'],$orderId,'สั่งซื้อสำเร็จ');notify_admins($orderId,'มีคำสั่งซื้อใหม่');
    if($stored)attach_payment_slip($order,(int)$u['id'],$stored);
    $hadSlip=$stored!==null;db()->commit();$stored=null;$_SESSION['cart']=[];$_SESSION['checkout_token']=bin2hex(random_bytes(32));
    flash($method==='COD'?'รับคำสั่งซื้อแล้ว · ชำระเงินเมื่อได้รับสินค้า':(!empty($hadSlip)?'รับคำสั่งซื้อแล้ว · รอตรวจสอบการชำระเงิน':'รับคำสั่งซื้อแล้ว · กรุณาชำระและแนบหลักฐานในหน้ารายละเอียด'));go('order_detail.php?id='.$orderId);
 }catch(Throwable $exception){
    if(db()->inTransaction())db()->rollBack();discard_payment_slip($stored);$stored=null;
    if($exception instanceof PDOException){error_log('Checkout failed: '.$exception->getCode());$existing=q('SELECT id FROM orders WHERE token=? AND user_id=?',[$token??'',$u['id']])->fetchColumn();if($existing)go('order_detail.php?id='.$existing);$error='ไม่สามารถบันทึกคำสั่งซื้อได้ กรุณาตรวจสอบประวัติก่อนลองใหม่';}
    else $error=$exception->getMessage();
 }
}
$items=cart_items();$sum=0;foreach($items as $item)$sum+=cents($item['price'])*$item['quantity'];$ship=$sum>=100000?0:5000;
$services=commerce_shipping_services();$quoteError='';$quote=null;$serviceId=(int)post('shipping_service_id')?:($services?(int)$services[0]['id']:0);
try{$quote=commerce_quote($items,(int)$u['id'],$shippingValues['province'],$serviceId,post('coupon_codes'),post('skip_auto')==='1',false,post('payment_method')?:(!empty($bank['cod_enabled'])?'COD':(bank_available($bank)?'BANK':(promptpay_available($bank)?'PROMPTPAY':''))));}catch(RuntimeException $exception){$quoteError=$exception->getMessage();}
require 'includes/header.php';
?>
<div class="page-heading"><h1><?=e(t('จัดส่งความสุขถึงบ้าน'))?></h1><p><?=e(t('ตรวจสอบข้อมูลและเลือกวิธีชำระเงินที่สะดวก'))?></p></div>
<?php if($error): ?><p class="error" role="alert"><?=e(t($error))?></p><?php endif ?>
<?php if(!$items): ?><p><?=e(t('ไม่มีสินค้าในตะกร้า'))?> <a href="<?=url('products.php')?>"><?=e(t('เลือกสินค้า'))?></a></p><?php else: ?>
<div class="two-col payment-checkout"><form method="post" enctype="multipart/form-data" class="panel" data-payment-form>
<?=csrf()?><input type="hidden" name="token" value="<?=e($_SESSION['checkout_token'])?>">
<?php require 'includes/checkout_addresses.php'; ?>
<label><?=e(t('หมายเหตุคำสั่งซื้อ'))?><textarea name="note" maxlength="2000"><?=e(post('note'))?></textarea></label>
<?php require 'includes/checkout_commerce.php'; ?>
<button type="submit" class="checkout-submit"><span><?=e(t('ยอดชำระ'))?> <strong data-money="total"><?=money(($quote['total']??0)/100)?></strong></span><span><?=e(t('ยืนยันคำสั่งซื้อ'))?></span></button></form>
<?php require 'includes/checkout_summary.php'; ?></div><?php endif ?>
<?php require 'includes/footer.php'; ?>
