<?php
declare(strict_types=1);
function cart_identity(int|string $key): array {if(preg_match('/^([1-9][0-9]*)(?::([1-9][0-9]*))?$/D',(string)$key,$match))return [(int)$match[1],isset($match[2])?(int)$match[2]:0];throw new RuntimeException('รหัสสินค้าในตะกร้าไม่ถูกต้อง');}
function inventory_record(int $productId,?int $variant,int $delta,string $kind,?int $order,int $actor,string $note=''): void {
    $product=q('SELECT stock FROM products WHERE id=?',[$productId])->fetchColumn();$balance=$variant?q('SELECT stock FROM product_variants WHERE id=?',[$variant])->fetchColumn():$product;
    q('INSERT INTO inventory_movements(product_id,variant_id,order_id,actor_id,kind,quantity,balance,product_balance,note) VALUES (?,?,?,?,?,?,?,?,?)',[$productId,$variant,$order,$actor,$kind,$delta,$balance,$product,$note]);
}
function inventory_reserve(array $product,int $qty,int $order,int $actor): void {
    if(!db()->inTransaction()||$qty<1)throw new RuntimeException('Stock transaction required');
    if(q('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?',[$qty,$product['id'],$qty])->rowCount()!==1)throw new RuntimeException('สินค้าไม่พร้อมจำหน่ายหรือสต็อกไม่เพียงพอ กรุณาตรวจสอบตะกร้า');
    $variant=(int)($product['variant_id']??0);if($variant&&q('UPDATE product_variants SET stock=stock-? WHERE id=? AND product_id=? AND stock>=?',[$qty,$variant,$product['id'],$qty])->rowCount()!==1)throw new RuntimeException('สี/ไซซ์ที่เลือกมีสต๊อกไม่เพียงพอ');
    inventory_record((int)$product['id'],$variant?:null,-$qty,'reserve',$order,$actor);
    $after=(int)q('SELECT stock FROM products WHERE id=?',[$product['id']])->fetchColumn();$threshold=(int)($product['low_stock_threshold']??5);
    if($after<=$threshold&&$after+$qty>$threshold)notify_admins($order,'สินค้าใกล้หมด: '.$product['name'].' คงเหลือ '.$after);
}
function inventory_release(array $item,int $order,int $actor): void {
    $variant=(int)($item['variant_id']??0);$qty=(int)$item['quantity'];
    $product=q('SELECT * FROM products WHERE id=? FOR UPDATE',[$item['product_id']])->fetch();$restoreParent=true;if(!$variant&&!empty($product['variants_enabled'])){inventory_record((int)$item['product_id'],null,$qty,'unallocated_return',$order,$actor,'คืนสินค้ารุ่นเดิมก่อนแยกสี/ไซซ์ ต้องตรวจนับและจัดเข้ารุ่นโดยผู้ดูแล');notify_admins($order,'มีสินค้าคืนที่ต้องตรวจนับเข้ารุ่น: '.$product['name']);return;}
    if($variant){$row=q('SELECT * FROM product_variants WHERE id=? AND product_id=? FOR UPDATE',[$variant,$item['product_id']])->fetch();if(!$row)throw new RuntimeException('ไม่พบสต๊อกรุ่นสินค้า');q('UPDATE product_variants SET stock=stock+? WHERE id=?',[$qty,$variant]);$restoreParent=(bool)$row['active'];}
    if($restoreParent)q('UPDATE products SET stock=stock+? WHERE id=?',[$qty,$item['product_id']]);
    inventory_record((int)$item['product_id'],$variant?:null,$qty,'release',$order,$actor,'คืนจากการยกเลิกคำสั่งซื้อ');
}
function inventory_adjust(int $productId,int $actor,int $newStock,string $note,int $variantId=0): void {
    if(!db()->inTransaction())throw new RuntimeException('Stock transaction required');
    $product=q('SELECT * FROM products WHERE id=? FOR UPDATE',[$productId])->fetch();if(!$product)throw new RuntimeException('ไม่พบสินค้า');
    if($newStock<0||$newStock>2147483647||mb_strlen($note)>500)throw new RuntimeException('จำนวนหรือรายละเอียดสต๊อกไม่ถูกต้อง');
    if($variantId){$variant=q('SELECT * FROM product_variants WHERE id=? AND product_id=? FOR UPDATE',[$variantId,$productId])->fetch();if(!$variant)throw new RuntimeException('ไม่พบสี/ไซซ์');$old=(int)$variant['stock'];q('UPDATE product_variants SET stock=? WHERE id=?',[$newStock,$variantId]);q('UPDATE products SET stock=(SELECT COALESCE(SUM(stock),0) FROM product_variants WHERE product_id=? AND active=1) WHERE id=?',[$productId,$productId]);}
    else{if($product['variants_enabled'])throw new RuntimeException('สินค้านี้ต้องปรับสต๊อกแยกสี/ไซซ์');$old=(int)$product['stock'];q('UPDATE products SET stock=? WHERE id=?',[$newStock,$productId]);}
    inventory_record($productId,$variantId?:null,$newStock-$old,'adjust',null,$actor,$note);
}
