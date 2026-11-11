<?php
require 'bootstrap.php';$error='';$id=(int)($_GET['product_id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
    $id=(int)post('product_id');db()->beginTransaction();$product=q('SELECT * FROM products WHERE id=? FOR UPDATE',[$id])->fetch();if(!$product)throw new RuntimeException('ไม่พบสินค้า');
    if(post('action')==='variant'){
        $color=post('color');$size=post('size');$sku=post('sku');$stock=filter_var(post('stock'),FILTER_VALIDATE_INT);
        if(($color===''&&$size==='')||mb_strlen($color)>80||mb_strlen($size)>80||$sku===''||strlen($sku)>80||$stock===false||$stock<0||$stock>2147483647)throw new RuntimeException('ตรวจสี ไซซ์ SKU และสต๊อก');
        if(!$product['variants_enabled']&&post('convert_confirm')!=='1')throw new RuntimeException('ยืนยันการเปลี่ยนจากสต๊อกกลางเป็นสี/ไซซ์ก่อน');
        q('INSERT INTO product_variants(product_id,color,size,sku,stock) VALUES (?,?,?,?,?)',[$id,$color,$size,$sku,$stock]);$variant=(int)db()->lastInsertId();q('UPDATE products SET variants_enabled=1,stock=(SELECT SUM(stock) FROM product_variants WHERE product_id=? AND active=1) WHERE id=?',[$id,$id]);inventory_record($id,$variant,$stock,'variant_created',null,(int)$admin['id'],'ตั้งสต๊อกแยกสี/ไซซ์โดย Admin');
    }elseif(post('action')==='adjust'){
        $stock=filter_var(post('stock'),FILTER_VALIDATE_INT);if($stock===false||post('note')==='')throw new RuntimeException('กรอกจำนวนเต็มและเหตุผล');inventory_adjust($id,(int)$admin['id'],$stock,post('note'),(int)post('variant_id'));
    }else throw new RuntimeException('การดำเนินการไม่ถูกต้อง');
    db()->commit();flash('บันทึกสต๊อกและประวัติแล้ว');go('admin/inventory.php?product_id='.$id);
 }catch(Throwable $ex){if(db()->inTransaction())db()->rollBack();$error=$ex instanceof PDOException?'บันทึกไม่สำเร็จ SKU หรือสี/ไซซ์อาจซ้ำ':$ex->getMessage();}
}
admin_header('จัดการสต๊อก');if($error)echo '<p class="error">'.e(t($error)).'</p>';
?>
<form class="filters"><select name="product_id"><?php foreach(q('SELECT id,name,stock FROM products ORDER BY id') as $row): ?><option value="<?=$row['id']?>" <?=$id===$row['id']?'selected':''?>><?=e($row['name'])?> · <?=$row['stock']?></option><?php endforeach ?></select><button>ดูสต๊อก</button></form>
<?php $product=$id?q('SELECT * FROM products WHERE id=?',[$id])->fetch():null;if($product):$variants=q('SELECT * FROM product_variants WHERE product_id=? ORDER BY id',[$id])->fetchAll(); ?>
<section class="panel"><h2><?=e($product['name'])?></h2><p>พร้อมขายรวม <?=$product['stock']?> ชิ้น · แจ้งเตือนเมื่อไม่เกิน <?=$product['low_stock_threshold']?> <?=e(t('ชิ้น'))?></p>
<?php if(!$product['variants_enabled']): ?><form method="post"><?=csrf()?><input type="hidden" name="product_id" value="<?=$id?>"><input type="hidden" name="action" value="adjust"><?php field('stock','สต๊อกใหม่','number',$product['stock']);field('note','เหตุผลปรับสต๊อก'); ?><button>ปรับสต๊อก</button></form><?php endif ?>
<?php foreach($variants as $variant): ?><form method="post" class="panel"><?=csrf()?><input type="hidden" name="product_id" value="<?=$id?>"><input type="hidden" name="variant_id" value="<?=$variant['id']?>"><input type="hidden" name="action" value="adjust"><h3><?=e($variant['color'].' / '.$variant['size'].' · '.$variant['sku'])?></h3><?php field('stock','สต๊อกใหม่','number',$variant['stock']);field('note','เหตุผลปรับสต๊อก'); ?><button><?=e(t('บันทึก'))?></button></form><?php endforeach ?></section>
<form method="post" class="panel"><?=csrf()?><input type="hidden" name="product_id" value="<?=$id?>"><input type="hidden" name="action" value="variant"><h2>เพิ่มสี / ไซซ์</h2><?php field('color','สี','text','',false);field('size','ไซซ์','text','',false);field('sku','SKU รุ่นสินค้า');field('stock','สต๊อกของรุ่น','number','0'); ?><?php if(!$product['variants_enabled']): ?><label class="checkbox"><input type="checkbox" name="convert_confirm" value="1" required> เปลี่ยนเป็นสต๊อกแยกรุ่น ยอดพร้อมขายกลางจะใช้ผลรวมรุ่นที่เพิ่มแทนยอดเดิม โปรดแบ่งจำนวนจริงให้ครบ</label><?php endif ?><button>เพิ่มรุ่นสินค้า</button></form>
<div class="table-wrap"><table><tr><th>วันเวลา</th><th>ประเภท</th><th>เปลี่ยน</th><th><?=e(t('คงเหลือ'))?></th><th>หมายเหตุ</th></tr><?php foreach(q('SELECT * FROM inventory_movements WHERE product_id=? ORDER BY id DESC LIMIT 100',[$id]) as $movement): ?><tr><td><?=e($movement['created_at'])?></td><td><?=e($movement['kind'])?></td><td><?=$movement['quantity']?></td><td><?=$movement['balance']?></td><td><?=e($movement['note'])?></td></tr><?php endforeach ?></table></div><?php endif ?>
<h2>สินค้าใกล้หมด</h2><?php foreach(q('SELECT id,name,stock FROM products WHERE active=1 AND stock<=low_stock_threshold') as $low): ?><p><a href="?product_id=<?=$low['id']?>"><?=e($low['name'])?> · <?=$low['stock']?></a></p><?php endforeach ?><?php admin_footer(); ?>
