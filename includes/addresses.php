<?php
declare(strict_types=1);
function address_fields(): array {return ['recipient'=>'ชื่อ–นามสกุลผู้รับ','phone'=>'เบอร์โทรศัพท์','address_line'=>'บ้านเลขที่ หมู่ ซอย ถนน','subdistrict'=>'ตำบล / แขวง','district'=>'อำเภอ / เขต','province'=>'จังหวัด','postcode'=>'รหัสไปรษณีย์'];}
function validate_shipping_address(array $input): array {
    $values=[];
    foreach(array_merge(['label'=>60,'recipient'=>120,'phone'=>30,'address_line'=>2000,'subdistrict'=>120,'district'=>120,'province'=>120,'postcode'=>5,'details'=>1000,'map_url'=>2000]) as $key=>$limit){
        $values[$key]=trim(is_string($input[$key]??null)?$input[$key]:'');
        if(mb_strlen($values[$key])>$limit||preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/',$values[$key]))throw new RuntimeException('กรุณาตรวจสอบความยาวและรูปแบบข้อมูลที่อยู่');
    }
    foreach(address_fields() as $key=>$label)if($values[$key]==='')throw new RuntimeException(t('กรุณากรอก').' '.t($label));
    if(!preg_match('/^[0-9]{9,10}$/D',$values['phone'])||!preg_match('/^[0-9]{5}$/D',$values['postcode']))throw new RuntimeException('เบอร์โทรศัพท์ต้องเป็นตัวเลข 9–10 หลัก และรหัสไปรษณีย์ 5 หลัก');
    if($values['map_url']!==''&&!valid_delivery_map($values['map_url']))throw new RuntimeException('ลิงก์แผนที่ต้องเป็น Google Maps แบบ https');
    return array_merge($values,shipping_coordinates($input));
}
function member_addresses(int $userId): array {return q('SELECT * FROM user_addresses WHERE user_id=? ORDER BY is_default DESC,id DESC',[$userId])->fetchAll();}
function address_lock_member(int $userId): void {if(!q('SELECT id FROM users WHERE id=? AND active=1 FOR UPDATE',[$userId])->fetchColumn())throw new RuntimeException('ไม่พบบัญชีที่ใช้งาน');}
function address_fingerprint(array $values): string {$copy=$values;unset($copy['label']);ksort($copy);return hash('sha256',json_encode($copy,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));}
// Caller holds a transaction and member lock. No function silently imports orders.
function save_member_address(int $userId,array $values,bool $makeDefault=false,int $id=0): int {
    if(!db()->inTransaction())throw new RuntimeException('Address transaction required');
    if($id&&!q('SELECT id FROM user_addresses WHERE id=? AND user_id=? FOR UPDATE',[$id,$userId])->fetchColumn())throw new RuntimeException('ไม่พบที่อยู่ของคุณ');
    $fingerprint=address_fingerprint($values);$duplicate=q('SELECT id FROM user_addresses WHERE user_id=? AND fingerprint=?',[$userId,$fingerprint])->fetchColumn();
    if($duplicate&&$id&&(int)$duplicate!==$id)throw new RuntimeException('มีที่อยู่นี้อยู่แล้ว กรุณาใช้รายการเดิม');
    if(!$id&&$duplicate){$id=(int)$duplicate;if($values['label']==='')$values['label']=(string)q('SELECT label FROM user_addresses WHERE id=? AND user_id=?',[$id,$userId])->fetchColumn();}
    if($makeDefault)q('UPDATE user_addresses SET is_default=0 WHERE user_id=?',[$userId]);
    if($id){
        q('UPDATE user_addresses SET label=?,recipient=?,phone=?,address_line=?,subdistrict=?,district=?,province=?,postcode=?,details=?,map_url=?,fingerprint=? WHERE id=? AND user_id=?',[$values['label'],$values['recipient'],$values['phone'],$values['address_line'],$values['subdistrict'],$values['district'],$values['province'],$values['postcode'],$values['details'],$values['map_url'],$fingerprint,$id,$userId]);
    }else{
        q('INSERT INTO user_addresses(user_id,label,recipient,phone,address_line,subdistrict,district,province,postcode,details,map_url,fingerprint) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',[$userId,$values['label'],$values['recipient'],$values['phone'],$values['address_line'],$values['subdistrict'],$values['district'],$values['province'],$values['postcode'],$values['details'],$values['map_url'],$fingerprint]);$id=(int)db()->lastInsertId();
    }
    $hasDefault=(bool)q('SELECT id FROM user_addresses WHERE user_id=? AND is_default=1',[$userId])->fetchColumn();
    if($makeDefault||!$hasDefault)q('UPDATE user_addresses SET is_default=1 WHERE id=? AND user_id=?',[$id,$userId]);
    q('UPDATE user_addresses SET latitude=?,longitude=? WHERE id=? AND user_id=?',[$values['latitude']??null,$values['longitude']??null,$id,$userId]);return $id;
}
function saved_address_from_order(array $order): array {
    $map=$order['map_url']??'';$line=$order['address'];
    if(valid_delivery_map($line)){$map=$map?:$line;$line='';}
    return ['label'=>'','recipient'=>$order['recipient'],'phone'=>$order['phone'],'address_line'=>$line,'subdistrict'=>$order['subdistrict'],'district'=>$order['district'],'province'=>$order['province'],'postcode'=>$order['postcode'],'details'=>$order['address_note']??'','map_url'=>$map,'latitude'=>$order['latitude']??null,'longitude'=>$order['longitude']??null];
}
function address_summary_html(array $address): string {return '<p>'.e($address['recipient']).' · '.e($address['phone']).'</p><p>'.nl2br(e($address['address_line'])).'<br>'.e($address['subdistrict'].' '.$address['district'].' '.$address['province'].' '.$address['postcode']).'</p>'.($address['details']!==''?'<p class="address-details">'.nl2br(e($address['details'])).'</p>':'');}
