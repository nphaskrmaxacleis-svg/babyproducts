<?php
require 'includes/functions.php';$u=auth();header('Content-Type: application/json');header('Cache-Control: private, no-store');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('{"ok":false}');}verify_csrf();
try{
    $items=cart_items();$services=commerce_shipping_services();$id=(int)post('shipping_service_id')?:($services?(int)$services[0]['id']:0);
    $quote=commerce_quote($items,(int)$u['id'],post('province'),$id,post('coupon_codes'),post('skip_auto')==='1',false,post('payment_method')?:'COD');$settings=bank_settings();$cod=true;$reason='';
    try{commerce_check_cod($settings,$quote,post('province'));}catch(RuntimeException $error){$cod=false;$reason=$error->getMessage();}
    $public=array_intersect_key($quote,array_flip(['subtotal','product_discount','coupon_discount','shipping_base','shipping_discount','shipping','total','applied','cod_fee','net_before_tax','vat_amount','tax_registered','tax_rate_bps']));
    echo json_encode(['ok'=>true,'quote'=>$public,'token'=>commerce_quote_token($quote,(int)$u['id']),'cod_available'=>$cod,'cod_reason'=>t($reason),'promptpay_payload'=>promptpay_available($settings)&&$quote['total']>0?promptpay_payload($settings['promptpay_type'],$settings['promptpay_recipient'],$quote['total']):null],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_THROW_ON_ERROR);
}catch(Throwable $error){http_response_code(422);echo json_encode(['ok'=>false,'message'=>$error instanceof PDOException?t('ไม่สามารถคำนวณยอดได้ กรุณาลองใหม่'):t($error->getMessage())],JSON_UNESCAPED_UNICODE);}
