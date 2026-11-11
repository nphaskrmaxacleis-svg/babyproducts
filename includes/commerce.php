<?php
declare(strict_types=1);
require_once __DIR__.'/taxes.php';
function commerce_money_input(string $input): int {if(!preg_match('/^[0-9]{1,9}(?:\.[0-9]{1,2})?$/D',$input))throw new RuntimeException('จำนวนเงินต้องเป็นตัวเลขทศนิยมไม่เกิน 2 ตำแหน่ง');[$whole,$fraction]=array_pad(explode('.',$input),2,'');return (int)$whole*100+(int)str_pad($fraction,2,'0');}
function commerce_provinces(string $value): array {return array_values(array_unique(array_filter(array_map('trim',preg_split('/[\r\n,]+/u',$value)?:[]))));}
function commerce_area_allowed(?string $areas,string $province): bool {$list=commerce_provinces($areas??'');return !$list||in_array(trim($province),$list,true);}
function commerce_shipping_services(): array {return q('SELECT * FROM shipping_services WHERE active=1 ORDER BY id')->fetchAll();}
function commerce_coupon_codes(string $text): array {
    $codes=array_values(array_filter(array_map('trim',explode(',',strtoupper($text)))));
    if(count($codes)>2||count(array_unique($codes))!==count($codes))throw new RuntimeException('ใช้คูปองได้ไม่เกิน 2 โค้ดและห้ามซ้ำ');
    foreach($codes as $code)if(!preg_match('/^[A-Z0-9_-]{2,40}$/D',$code))throw new RuntimeException('รูปแบบคูปองไม่ถูกต้อง');return $codes;
}
function commerce_eligible_ids(array $promotion,array $items): array {
    $products=q('SELECT product_id FROM promotion_products WHERE promotion_id=?',[$promotion['id']])->fetchAll(PDO::FETCH_COLUMN);
    $categories=q('SELECT category_id FROM promotion_categories WHERE promotion_id=?',[$promotion['id']])->fetchAll(PDO::FETCH_COLUMN);$ids=[];
    foreach($items as $id=>$item)if((!$products||in_array($item['product_id'],$products))&&(!$categories||in_array($item['category_id'],$categories)))$ids[]=$id;
    return $ids;
}
function commerce_promotion_error(array $promotion,int $userId,int $gross,int $priorOrders,array $items): ?string {
    $now=time();if(!$promotion['active']||strtotime($promotion['starts_at'])>$now||strtotime($promotion['ends_at'])<$now)return 'ยังไม่เริ่มหรือหมดอายุแล้ว';
    if($gross<(int)$promotion['min_subtotal'])return 'ยอดซื้อไม่ถึงขั้นต่ำ';
    if(($promotion['audience']==='new'&&$priorOrders>0)||($promotion['audience']==='returning'&&$priorOrders===0))return 'ไม่ตรงกลุ่มสมาชิก';
    $eligible=commerce_eligible_ids($promotion,$items);$quantity=0;foreach($eligible as $id)$quantity+=$items[$id]['quantity'];
    if(!$eligible||$quantity<(int)$promotion['min_quantity'])return 'สินค้าหรือจำนวนไม่ตรงเงื่อนไข';
    $used=(int)q('SELECT COUNT(*) FROM promotion_redemptions WHERE promotion_id=? AND released_at IS NULL',[$promotion['id']])->fetchColumn();
    $personal=(int)q('SELECT COUNT(*) FROM promotion_redemptions WHERE promotion_id=? AND user_id=? AND released_at IS NULL',[$promotion['id'],$userId])->fetchColumn();
    if($promotion['total_limit']!==null&&$used>=(int)$promotion['total_limit'])return 'สิทธิ์เต็มแล้ว';
    if($promotion['per_user_limit']!==null&&$personal>=(int)$promotion['per_user_limit'])return 'คุณใช้ครบสิทธิ์แล้ว';return null;
}
function commerce_discount_amount(array $promotion,array $items,array $remaining,array $eligible,int $shipping): int {
    $sum=0;foreach($eligible as $id)$sum+=$remaining[$id];$value=(int)$promotion['value'];$discount=0;
    switch($promotion['kind']){
        case 'fixed':$discount=min($value,$sum);break;
        case 'percent':$discount=intdiv($sum*$value,10000);break;
        case 'shipping':$discount=$value>0?min($shipping,$value):$shipping;break;
        case 'unit_price':case 'flash_price':case 'bundle_price':$discount=array_sum(commerce_price_weights($promotion,$items,$remaining,$eligible));break;
    }
    if((int)$promotion['max_discount']>0)$discount=min($discount,(int)$promotion['max_discount']);return max(0,$discount);
}
function commerce_price_weights(array $promotion,array $items,array $remaining,array $eligible): array {
    $weights=[];foreach($eligible as $id){$item=$items[$id];$target=(int)$promotion['value']*$item['quantity'];if($promotion['kind']==='bundle_price'){$size=max(2,(int)$promotion['min_quantity']);$groups=intdiv($item['quantity'],$size);$target=$groups*(int)$promotion['value']+($item['quantity']-$groups*$size)*$item['unit'];}$weights[$id]=max(0,$remaining[$id]-$target);}return $weights;
}
function commerce_allocate(array &$remaining,array $eligible,int $discount,?array $weights=null): void {
    $weights??=array_intersect_key($remaining,array_flip($eligible));$sum=array_sum($weights);if($sum<=0||$discount<=0)return;$discount=min($discount,$sum);$left=$discount;$shares=[];
    foreach($eligible as $id){$shares[$id]=intdiv($weights[$id]*$discount,$sum);$left-=$shares[$id];}
    foreach($eligible as $id){if($left>0&&$shares[$id]<$weights[$id]){$shares[$id]++;$left--;}$remaining[$id]-=$shares[$id];}
}
function commerce_quote(array $rows,int $userId,string $province,int $serviceId,string $couponText,bool $skipAuto=false,bool $lock=false,string $method='COD'): array {
    $services=q('SELECT * FROM shipping_services WHERE active=1 ORDER BY id'.($lock?' FOR UPDATE':''))->fetchAll();$service=null;
    foreach($services as $candidate)if((int)$candidate['id']===$serviceId)$service=$candidate;
    if(!$service)throw new RuntimeException('กรุณาเลือกบริการขนส่งที่เปิดใช้งาน');
    if(!commerce_area_allowed($service['provinces'],$province))throw new RuntimeException('บริการขนส่งนี้ไม่รองรับจังหวัดที่เลือก');
    $items=[];$remaining=[];$gross=0;
    foreach($rows as $row){$id=$row['cart_key']??(int)$row['id'];$unit=cents($row['price']);$qty=(int)$row['quantity'];if($qty<1||$qty>10000)throw new RuntimeException('จำนวนสินค้าไม่ถูกต้อง');$line=$unit*$qty;$items[$id]=['product_id'=>(int)$row['id'],'unit'=>$unit,'quantity'=>$qty,'category_id'=>(int)$row['category_id'],'gross'=>$line];$remaining[$id]=$line;$gross+=$line;}
    if(!$items||$gross>99999999999)throw new RuntimeException('ยอดสั่งซื้อไม่ถูกต้อง');
    $codes=commerce_coupon_codes($couponText);$promotions=q('SELECT * FROM promotions WHERE active=1 ORDER BY id'.($lock?' FOR UPDATE':''))->fetchAll();
    $prior=(int)q('SELECT COUNT(*) FROM orders WHERE user_id=? AND status<>"cancelled"',[$userId])->fetchColumn();$coupons=[];$auto=null;$best=0;$eligibleCache=[];
    foreach($promotions as $promotion){
        $reason=commerce_promotion_error($promotion,$userId,$gross,$prior,$items);
        if($promotion['code']!==null){if(in_array($promotion['code'],$codes,true)){if($reason)throw new RuntimeException(t('โค้ดคูปอง').' '.$promotion['code'].': '.t($reason));$coupons[$promotion['code']]=$promotion;}continue;}
        if($skipAuto||$reason||$promotion['kind']==='shipping')continue;
        $eligible=commerce_eligible_ids($promotion,$items);$discount=commerce_discount_amount($promotion,$items,$remaining,$eligible,0);
        if($discount>$best){$best=$discount;$auto=$promotion;}
    }
    foreach($codes as $code)if(!isset($coupons[$code]))throw new RuntimeException('คูปอง '.$code.' ไม่พบหรือปิดใช้งาน');
    $selected=array_values($coupons);if($auto)array_unshift($selected,$auto);
    if(count($selected)>1)foreach($selected as $promotion)if(!$promotion['stackable'])throw new RuntimeException('โปรโมชั่นนี้ใช้ร่วมกับรายการอื่นไม่ได้ ลบคูปองหรือเลือกไม่ใช้โปรอัตโนมัติ');
    $productDiscount=0;$couponDiscount=0;$applied=[];
    if($auto){$eligible=commerce_eligible_ids($auto,$items);$weights=in_array($auto['kind'],['unit_price','flash_price','bundle_price'],true)?commerce_price_weights($auto,$items,$remaining,$eligible):null;commerce_allocate($remaining,$eligible,$best,$weights);$productDiscount=$best;$applied[]=['id'=>(int)$auto['id'],'name'=>$auto['name'],'code'=>null,'discount'=>$best,'kind'=>$auto['kind']];}
    foreach($codes as $code){$promotion=$coupons[$code];if($promotion['kind']==='shipping')continue;$eligible=commerce_eligible_ids($promotion,$items);$discount=commerce_discount_amount($promotion,$items,$remaining,$eligible,0);if($discount<=0)throw new RuntimeException('คูปอง '.$code.' ไม่มีส่วนลดในรายการนี้');commerce_allocate($remaining,$eligible,$discount,in_array($promotion['kind'],['unit_price','flash_price','bundle_price'],true)?commerce_price_weights($promotion,$items,$remaining,$eligible):null);$couponDiscount+=$discount;$applied[]=['id'=>(int)$promotion['id'],'name'=>$promotion['name'],'code'=>$code,'discount'=>$discount,'kind'=>$promotion['kind']];}
    $shippingBase=cents($service['fee']);$shipping=$service['free_min']!==null&&array_sum($remaining)>=cents($service['free_min'])?0:$shippingBase;
    foreach($codes as $code){$promotion=$coupons[$code];if($promotion['kind']!=='shipping')continue;$eligible=commerce_eligible_ids($promotion,$items);$discount=commerce_discount_amount($promotion,$items,$remaining,$eligible,$shipping);if($discount<=0)throw new RuntimeException('คูปองส่งฟรีไม่มีส่วนลดเพิ่มเติม เนื่องจากค่าจัดส่งเป็นศูนย์');$shipping-=$discount;$applied[]=['id'=>(int)$promotion['id'],'name'=>$promotion['name'],'code'=>$code,'discount'=>$discount,'kind'=>'shipping'];}
    $result=['subtotal'=>$gross,'product_discount'=>$productDiscount,'coupon_discount'=>$couponDiscount,'shipping_base'=>$shippingBase,'shipping_discount'=>$shippingBase-$shipping,'shipping'=>$shipping,'total'=>array_sum($remaining)+$shipping,'service'=>$service,'applied'=>$applied,'line_totals'=>$remaining,'line_gross'=>array_map(fn($item)=>$item['gross'],$items)];
    return tax_quote($result,$rows,$method);
}
function commerce_check_cod(array $settings,array $quote,string $province): void {
    if(empty($settings['cod_enabled']))throw new RuntimeException('ร้านค้าปิดบริการเก็บเงินปลายทาง');
    if(!$quote['service']['cod_allowed']||!commerce_area_allowed($settings['cod_provinces']??null,$province)||$quote['total']<cents($settings['cod_min'])||$quote['total']>cents($settings['cod_max']))throw new RuntimeException('COD ไม่รองรับพื้นที่ บริการขนส่ง หรือยอดสุทธิของคำสั่งซื้อนี้');
}
function commerce_record_redemptions(int $orderId,int $userId,array $quote): void {foreach($quote['applied'] as $promotion)q('INSERT INTO promotion_redemptions(promotion_id,order_id,user_id,discount_amount) VALUES (?,?,?,?)',[$promotion['id'],$orderId,$userId,$promotion['discount']]);}
function commerce_release_redemptions(int $orderId): void {q('UPDATE promotion_redemptions SET released_at=NOW() WHERE order_id=? AND released_at IS NULL',[$orderId]);}
function commerce_quote_token(array $quote,int $userId): string {
    $_SESSION['pricing_secret']??=bin2hex(random_bytes(32));
    return hash_hmac('sha256',json_encode([$userId,$quote['service']['id'],$quote['subtotal'],$quote['product_discount'],$quote['coupon_discount'],$quote['shipping_base'],$quote['shipping_discount'],$quote['total'],$quote['tax_snapshot']??[],$quote['cod_fee']??0,$quote['applied'],$quote['line_totals']],JSON_THROW_ON_ERROR),$_SESSION['pricing_secret']);
}
