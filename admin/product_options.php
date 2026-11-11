<?php
require 'bootstrap.php';$error='';$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
    $id=(int)post('id');if(!q('SELECT id FROM products WHERE id=?',[$id])->fetchColumn())throw new RuntimeException('ไม่พบสินค้า');
    $name=post('name');$description=post('description');$en=post('name_en');$enDescription=post('description_en');$threshold=filter_var(post('low_stock_threshold'),FILTER_VALIDATE_INT);
    if($name===''||mb_strlen($name)>190||$description===''||mb_strlen($description)>10000||mb_strlen($en)>190||mb_strlen($enDescription)>10000||$threshold===false||$threshold<0||$threshold>1000000)throw new RuntimeException('กรุณาตรวจข้อมูลสินค้าและระดับแจ้งเตือน');
    q('UPDATE products SET name=?,description=?,name_en=?,description_en=?,tax_exempt=?,low_stock_threshold=? WHERE id=?',[$name,$description,$en?:null,$enDescription?:null,isset($_POST['tax_exempt'])?1:0,$threshold,$id]);flash('บันทึกข้อมูลภาษาและภาษีสินค้าแล้ว');go('admin/product_options.php?id='.$id);
 }catch(RuntimeException $ex){$error=$ex instanceof PDOException?'บันทึกไม่สำเร็จ':$ex->getMessage();}
}
$product=$id?q('SELECT * FROM products WHERE id=?',[$id])->fetch():null;admin_header('ข้อมูลสินค้า TH / EN และภาษี');
?>
<form class="filters"><select name="id"><?php foreach(q('SELECT id,name FROM products ORDER BY id') as $row): ?><option value="<?=$row['id']?>" <?=$row['id']===$id?'selected':''?>><?=e($row['name'])?></option><?php endforeach ?></select><button><?=e(t('เลือกสินค้า'))?></button></form><?php if($error): ?><p class="error"><?=e(t($error))?></p><?php endif;if($product): ?><form method="post" class="panel"><?=csrf()?><input type="hidden" name="id" value="<?=$id?>"><?php foreach(['name'=>'ชื่อสินค้าไทย','name_en'=>'ชื่อสินค้าอังกฤษ (ไม่มีให้เว้นว่าง)'] as $key=>$label)field($key,$label,'text',$_SERVER['REQUEST_METHOD']==='POST'?post($key):($product[$key]??''),$key==='name');foreach(['description'=>'รายละเอียดไทย','description_en'=>'รายละเอียดอังกฤษ (ไม่มีให้เว้นว่าง)'] as $key=>$label): ?><label><?=e(t($label))?><textarea name="<?=$key?>" maxlength="10000"><?=e($_SERVER['REQUEST_METHOD']==='POST'?post($key):($product[$key]??''))?></textarea></label><?php endforeach ?><label class="checkbox"><input type="checkbox" name="tax_exempt" <?=($_SERVER['REQUEST_METHOD']==='POST'?isset($_POST['tax_exempt']):$product['tax_exempt'])?'checked':''?>> สินค้านี้ได้รับยกเว้น VAT ตามที่ผู้รับผิดชอบยืนยัน</label><?php field('low_stock_threshold','ระดับแจ้งเตือนใกล้หมด','number',$_SERVER['REQUEST_METHOD']==='POST'?post('low_stock_threshold'):$product['low_stock_threshold']); ?><button>บันทึกข้อมูล</button></form><?php endif ?><?php admin_footer(); ?>
