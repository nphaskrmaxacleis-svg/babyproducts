<?php
declare(strict_types=1);
require_once __DIR__.'/email.php';

const CONTACT_PRIVACY_VERSION='2026-11-09';
const CONTACT_MESSAGE_STATUSES=['new'=>'ใหม่','in_progress'=>'กำลังดำเนินการ','replied'=>'ตอบกลับแล้ว'];

function contact_channel_definitions(): array
{
    return [
        'phone'=>['label'=>'โทรศัพท์','limit'=>40],
        'email'=>['label'=>'อีเมล','limit'=>190],
        'hours'=>['label'=>'เวลาทำการ','limit'=>120],
        'line'=>['label'=>'LINE ID','limit'=>80],
        'address'=>['label'=>'ที่อยู่ร้านค้า','limit'=>500],
    ];
}

function store_contact_channels(): array
{
    static $channels;
    if($channels===null){$channels=[];foreach(q('SELECT * FROM store_contact_channels') as $row)$channels[$row['code']]=$row;}
    return $channels;
}

function contact_channel_confirmed(array $channel): bool
{
    if(!in_array($channel['code'],['line','address'],true))return true;
    return (bool)$channel['verified']&&!empty($channel['verified_by'])&&!empty($channel['verified_at']);
}

function public_contact_channels(): array
{
    $channels=store_contact_channels();$visible=[];
    foreach(contact_channel_definitions() as $code=>$definition){
        $channel=$channels[$code]??null;
        if($channel&&$channel['enabled']&&trim($channel['value'])!==''&&contact_channel_confirmed($channel))$visible[$code]=array_merge($channel,$definition);
    }
    return $visible;
}

function contact_phone_valid(string $phone): bool
{
    return (bool)preg_match('/^\+?[0-9][0-9 ()-]{7,28}$/D',$phone);
}

function contact_icon(string $code): string
{
    $paths=[
        'phone'=>'<path d="M6 3H3v3c0 8.3 6.7 15 15 15h3v-3l-5-2-2 2a14 14 0 0 1-8-8l2-2-2-5z"/>',
        'email'=>'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'hours'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'line'=>'<path d="M21 11a8 8 0 0 1-8 8H8l-5 3 1-6a8 8 0 0 1-1-5 9 9 0 0 1 18 0z"/><path d="M7 11h10"/>',
        'address'=>'<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0z"/><circle cx="12" cy="10" r="2.5"/>',
    ];
    return '<svg class="contact-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$code]??$paths['email']).'</svg>';
}

function save_store_contact_settings(array $input,int $adminId): void
{
    $pdo=db();$pdo->beginTransaction();
    try{
        $rows=[];foreach(q('SELECT * FROM store_contact_channels FOR UPDATE') as $row)$rows[$row['code']]=$row;
        $update=$pdo->prepare('UPDATE store_contact_channels SET value=?,enabled=?,verified=?,verified_by=?,verified_at=?,updated_by=? WHERE code=?');
        foreach(contact_channel_definitions() as $code=>$definition){
            if(!isset($rows[$code]))throw new RuntimeException('ไม่พบการตั้งค่าช่องทาง กรุณาตรวจสอบการติดตั้ง');
            $old=$rows[$code];$value=trim(is_string($input[$code]??null)?$input[$code]:'');
            if($code==='email'&&$value!=='')$value=normalize_login_email($value);
            $enabled=isset($input['show_'.$code])?1:0;
            if(mb_strlen($value)>$definition['limit']||($enabled&&$value===''))throw new RuntimeException('กรุณาตรวจสอบข้อมูล'.$definition['label'].' หรือปิดการแสดงช่องทางนี้');
            if($value!==''&&$code==='phone'&&!contact_phone_valid($value))throw new RuntimeException('รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง');
            if($value!==''&&$code==='email'&&!filter_var($value,FILTER_VALIDATE_EMAIL))throw new RuntimeException('รูปแบบอีเมลไม่ถูกต้อง');
            if($value!==''&&$code==='line'&&!preg_match('/^@?[A-Za-z0-9._-]{1,79}$/D',$value))throw new RuntimeException('รูปแบบ LINE ID ไม่ถูกต้อง');
            $critical=in_array($code,['line','address'],true);
            $same=$value===$old['value'];
            $verified=$critical?($same?(int)$old['verified']:0):1;
            $verifiedBy=$critical&&$same?$old['verified_by']:null;
            $verifiedAt=$critical&&$same?$old['verified_at']:null;
            if($critical&&isset($input['confirm_'.$code])){
                if($value==='')throw new RuntimeException('กรอกข้อมูลก่อนยืนยัน'.$definition['label']);
                $verified=1;$verifiedBy=$adminId;$verifiedAt=date('Y-m-d H:i:s');
            }
            if($critical&&isset($input['revoke_'.$code])){$verified=0;$verifiedBy=null;$verifiedAt=null;}
            $update->execute([$value,$enabled,$verified,$verifiedBy,$verifiedAt,$adminId,$code]);
        }
        $pdo->commit();
    }catch(Throwable $error){if($pdo->inTransaction())$pdo->rollBack();throw $error;}
}
