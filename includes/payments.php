<?php
declare(strict_types=1);
const PAYMENT_STATUSES=['unpaid'=>'ยังไม่ชำระ','pending_review'=>'ลูกค้าแจ้งชำระเงินแล้ว — รอตรวจสอบการชำระเงิน','paid'=>'ชำระเงินสำเร็จ','rejected'=>'หลักฐานไม่ผ่านการตรวจสอบ','refunded'=>'คืนเงินแล้ว'];
const ORDER_TRANSITIONS=['pending'=>['confirmed','cancelled'],'confirmed'=>['preparing','cancelled'],'preparing'=>['shipped','cancelled'],'shipped'=>['delivered'],'delivered'=>[],'cancelled'=>[]];
function bank_settings(): array {return q('SELECT * FROM payment_settings WHERE id=1')->fetch()?:[];}
function bank_available(array $bank): bool {return !empty($bank['enabled'])&&trim($bank['bank_name']??'')!==''&&trim($bank['account_name']??'')!==''&&trim($bank['account_number']??'')!=='';}
function payment_method_label(array $order): string {return t(['BANK'=>'โอนเงินผ่านธนาคาร','COD'=>'เก็บเงินปลายทาง (COD)','PROMPTPAY'=>'QR PromptPay','GATEWAY'=>'Payment Gateway'][$order['payment_method']]??'ไม่ทราบช่องทาง');}
function order_stage(array $order): string {
    if(in_array($order['status'],['cancelled','preparing','shipped','delivered'],true))return $order['status'];
    if(in_array($order['payment_method'],['BANK','PROMPTPAY'],true)){
        if($order['payment_status']==='pending_review')return 'payment_review';
        if($order['payment_status']==='paid')return 'paid';
        return 'awaiting_payment';
    }
    return $order['status']==='confirmed'?'confirmed':'pending';
}
const ORDER_STAGES=['pending'=>'รอยืนยันคำสั่งซื้อ','confirmed'=>'ยืนยันคำสั่งซื้อแล้ว','awaiting_payment'=>'รอชำระเงิน','payment_review'=>'รอตรวจสอบการชำระเงิน','paid'=>'ชำระเงินแล้ว','preparing'=>'กำลังเตรียมสินค้า','shipped'=>'จัดส่งสินค้าแล้ว','delivered'=>'จัดส่งสำเร็จ','cancelled'=>'ยกเลิกคำสั่งซื้อ'];
function payment_badge(array $order): string {return '<span class="payment-badge payment-'.e($order['payment_status']).'">'.e(t(PAYMENT_STATUSES[$order['payment_status']]??$order['payment_status'])).'</span>';}
function order_badge(array $order): string {return '<span class="payment-badge stage-'.e($order['status']).'">'.e(t(STATUSES[$order['status']]??$order['status'])).'</span>';}
function order_notify(int $userId,int $orderId,string $message): void {q('INSERT INTO notifications(user_id,order_id,message) VALUES (?,?,?)',[$userId,$orderId,$message]);}
function notify_admins(int $orderId,string $message): void {foreach(q('SELECT id FROM users WHERE role="admin" AND active=1') as $admin)order_notify((int)$admin['id'],$orderId,$message);}
function order_event(array $order,int $actor,string $event,string $note=''): void {q('INSERT INTO order_events(order_id,actor_id,event_type,order_status,payment_status,note) VALUES (?,?,?,?,?,?)',[$order['id'],$actor,$event,$order['status'],$order['payment_status'],$note]);}
function locked_order(int $id): array {$order=q('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id])->fetch();if(!$order)throw new RuntimeException('ไม่พบคำสั่งซื้อ');return $order;}
function store_payment_slip(array $file): string {
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new RuntimeException('กรุณาแนบสลิป JPG, PNG หรือ WEBP ขนาดไม่เกิน 5 MB');
    $temp=is_string($file['tmp_name']??null)?$file['tmp_name']:'';
    if(!is_uploaded_file($temp)||filesize($temp)>5*1024*1024)throw new RuntimeException('สลิปต้องมีขนาดไม่เกิน 5 MB');
    $ext=strtolower(pathinfo(is_string($file['name']??null)?$file['name']:'',PATHINFO_EXTENSION));
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($temp);$info=@getimagesize($temp);
    if(!in_array($ext,['jpg','jpeg','png','webp'],true)||!in_array($mime,['image/jpeg','image/png','image/webp'],true)||!$info||($info['mime']??'')!==$mime)throw new RuntimeException('ชนิดไฟล์สลิปไม่ถูกต้อง');
    if($info[0]>4096||$info[1]>4096||$info[0]*$info[1]>12000000)throw new RuntimeException('ภาพสลิปต้องไม่เกิน 4096 พิกเซลต่อด้าน และ 12 ล้านพิกเซล');
    $image=@imagecreatefromstring(file_get_contents($temp));if(!$image)throw new RuntimeException('ไม่สามารถอ่านภาพสลิปได้');
    $name=bin2hex(random_bytes(24)).'.png';$path=__DIR__.'/../storage/slips/'.$name;
    try{if(!imagepng($image,$path,6)){@unlink($path);throw new RuntimeException('ไม่สามารถบันทึกภาพสลิปได้');}}finally{imagedestroy($image);}
    return $name;
}
function discard_payment_slip(?string $name): void {if($name&&preg_match('/^[a-f0-9]{48}\.png$/D',$name))@unlink(__DIR__.'/../storage/slips/'.$name);}
function attach_payment_slip(array $order,int $actor,string $name): void {
    if(!in_array($order['payment_method'],['BANK','PROMPTPAY'],true)||$order['status']==='cancelled'||!in_array($order['payment_status'],['unpaid','rejected'],true))throw new RuntimeException('คำสั่งซื้อนี้ไม่สามารถแนบหลักฐานเพิ่มได้');
    $hash=hash_file('sha256',__DIR__.'/../storage/slips/'.$name);
    q('INSERT IGNORE INTO slip_fingerprints(content_hash) VALUES (?)',[$hash]);
    $existing=q('SELECT slip_id FROM slip_fingerprints WHERE content_hash=? FOR UPDATE',[$hash])->fetchColumn();
    if($existing)throw new RuntimeException('ภาพหลักฐานนี้เคยส่งแล้ว กรุณาใช้หลักฐานใหม่หรือติดต่อร้านค้า ไม่มีการยืนยันรับเงินจากภาพซ้ำ');
    q('INSERT INTO order_slips(order_id,stored_name,content_hash) VALUES (?,?,?)',[$order['id'],$name,$hash]);$slipId=(int)db()->lastInsertId();
    q('UPDATE slip_fingerprints SET slip_id=? WHERE content_hash=?',[$slipId,$hash]);
    q('UPDATE orders SET payment_status="pending_review" WHERE id=?',[$order['id']]);$order['payment_status']='pending_review';
    order_event($order,$actor,'slip_uploaded','แนบหลักฐานการโอนเงิน รอตรวจสอบยอดเข้าบัญชีจริง');
    order_notify((int)$order['user_id'],(int)$order['id'],'อัปโหลดสลิปสำเร็จ รอตรวจสอบการชำระเงิน');notify_admins((int)$order['id'],'มีสลิปโอนเงินรอตรวจสอบ');
}
function admin_order_action(int $id,array $actor): void {
    if(($actor['role']??'')!=='admin')throw new RuntimeException('ไม่มีสิทธิ์');
    $pdo=db();$pdo->beginTransaction();
    try{
        $order=locked_order($id);$action=post('action');$note='';$event='';
        if($action==='payment'){
            if($order['payment_method']!=='COD'||$order['status']!=='delivered'||$order['payment_status']!=='unpaid')throw new RuntimeException('ยืนยันรับเงิน COD ได้หลังจัดส่งสำเร็จ และยังไม่เคยรับเงินเท่านั้น');
            q('UPDATE orders SET payment_status="paid" WHERE id=?',[$id]);$order['payment_status']='paid';$event='cod_received';$note='ผู้ดูแลยืนยันได้รับเงิน COD จริงแล้ว';
        }elseif(in_array($action,['approve_slip','reject_slip'],true)){
            if(!in_array($order['payment_method'],['BANK','PROMPTPAY'],true)||$order['payment_status']!=='pending_review')throw new RuntimeException('ไม่มีหลักฐานที่รอตรวจสอบ');
            $slip=q('SELECT * FROM order_slips WHERE order_id=? ORDER BY id DESC LIMIT 1 FOR UPDATE',[$id])->fetch();
            if(!$slip||$slip['status']!=='pending_review'||(int)post('slip_id')!==(int)$slip['id'])throw new RuntimeException('หลักฐานเปลี่ยนไป กรุณาเปิดหน้าใหม่');
            if($action==='approve_slip'&&post('received_confirm')!=='1')throw new RuntimeException('ต้องตรวจยอดเงินเข้าบัญชีจริงและยืนยันก่อน');
            $note=$action==='reject_slip'?post('reason'):'ตรวจยอดเงินเข้าบัญชีจริงแล้ว';
            if($note===''||mb_strlen($note)>1000)throw new RuntimeException('กรุณาระบุเหตุผลไม่เกิน 1,000 ตัวอักษร');
            $status=$action==='approve_slip'?'paid':'rejected';
            q('UPDATE order_slips SET status=?,rejection_reason=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?',[$status==='paid'?'approved':'rejected',$status==='rejected'?$note:null,$actor['id'],$slip['id']]);
            q('UPDATE orders SET payment_status=? WHERE id=?',[$status,$id]);$order['payment_status']=$status;$event=$action;
        }elseif($action==='tracking'){
            if($order['status']==='cancelled')throw new RuntimeException('คำสั่งซื้อยกเลิกแล้ว');
            $carrier=post('carrier');$tracking=post('tracking_number');if($carrier===''||$tracking===''||mb_strlen($carrier)>100||mb_strlen($tracking)>100||preg_match('/[\x00-\x1f]/',$carrier.$tracking))throw new RuntimeException('กรอกบริษัทขนส่งและเลขพัสดุ ไม่เกิน 100 ตัวอักษร');
            $weight=post('package_weight');$dimensions=post('package_dimensions');if(($weight!==''&&!preg_match('/^[0-9]{1,6}(?:\.[0-9]{1,3})?$/D',$weight))||mb_strlen($dimensions)>120)throw new RuntimeException('น้ำหนักหรือขนาดพัสดุไม่ถูกต้อง');$limit=$order['shipping_service_id']?q('SELECT max_weight FROM shipping_services WHERE id=?',[$order['shipping_service_id']])->fetchColumn():null;if($limit&&$weight!==''&&(float)$weight>(float)$limit)throw new RuntimeException('น้ำหนักเกินขีดจำกัดบริการขนส่งที่เลือก');q('UPDATE orders SET carrier=?,tracking_number=?,package_weight=?,package_dimensions=? WHERE id=?',[$carrier,$tracking,$weight?:null,$dimensions,$id]);$event='tracking_updated';$note=$carrier.' · '.$tracking;
        }elseif($action==='refund'){
            if($order['status']!=='cancelled'||$order['payment_status']!=='paid'||post('refund_confirm')!=='1'||post('reason')===''||mb_strlen(post('reason'))>1000)throw new RuntimeException('บันทึกคืนเงินได้เฉพาะคำสั่งซื้อที่ยกเลิกและได้รับเงินแล้ว ต้องยืนยันคืนเงินจริงพร้อมรายละเอียด');
            q('UPDATE orders SET payment_status="refunded" WHERE id=?',[$id]);$order['payment_status']='refunded';$event='refund_recorded';$note=post('reason');
        }else{
            if($action!==''&&$action!=='status')throw new RuntimeException('การดำเนินการไม่ถูกต้อง');
            $new=post('status');if(!in_array($new,ORDER_TRANSITIONS[$order['status']]??[],true))throw new RuntimeException('ไม่สามารถเปลี่ยนสถานะตามที่เลือกได้');
            if($new==='preparing'&&$order['payment_method']!=='COD'&&$order['payment_status']!=='paid')throw new RuntimeException('ต้องยืนยันยอดโอนจริงก่อนเตรียมสินค้า');
            if($new==='shipped'&&(empty($order['carrier'])||empty($order['tracking_number'])))throw new RuntimeException('กรุณาบันทึกบริษัทขนส่งและเลขพัสดุก่อนจัดส่ง');
            if($new==='cancelled')commerce_release_redemptions($id);
            if($new==='cancelled')foreach(q('SELECT * FROM order_items WHERE order_id=? ORDER BY product_id',[$id]) as $item)inventory_release($item,$id,(int)$actor['id']);
            q('UPDATE orders SET status=? WHERE id=?',[$new,$id]);$shipment=match($new){'shipped'=>'shipped','delivered'=>'delivered','cancelled'=>'cancelled',default=>'not_shipped'};q('UPDATE orders SET shipment_status=?,shipped_at=CASE WHEN ?="shipped" THEN COALESCE(shipped_at,NOW()) ELSE shipped_at END,delivered_at=CASE WHEN ?="delivered" THEN COALESCE(delivered_at,NOW()) ELSE delivered_at END WHERE id=?',[$shipment,$new,$new,$id]);$order['status']=$new;$event='status_changed';$note=STATUSES[$new];
            if($new==='cancelled'&&$order['payment_status']==='paid')$note.=' · ต้องดำเนินการคืนเงินจริงแยกต่างหาก';
        }
        order_event($order,(int)$actor['id'],$event,$note);order_notify((int)$order['user_id'],$id,($event==='tracking_updated'?'อัปเดตเลขพัสดุ':($event==='status_changed'?STATUSES[$order['status']]:PAYMENT_STATUSES[$order['payment_status']])).($note!==''?' · '.$note:''));
        $pdo->commit();
    }catch(Throwable $error){if($pdo->inTransaction())$pdo->rollBack();throw $error;}
}
