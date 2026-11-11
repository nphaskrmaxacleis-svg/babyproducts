<?php
declare(strict_types=1);
function promptpay_available(array $settings): bool {return !empty($settings['promptpay_enabled'])&&trim($settings['promptpay_name']??'')!==''&&promptpay_valid_recipient($settings['promptpay_type']??'', $settings['promptpay_recipient']??'');}
function promptpay_valid_recipient(string $type,string $value): bool {
    if($type==='phone')return (bool)preg_match('/^0[689][0-9]{8}$/D',$value);
    if($type!=='national_id'||!preg_match('/^[1-9][0-9]{12}$/D',$value))return false;
    $sum=0;for($i=0;$i<12;$i++)$sum+=(int)$value[$i]*(13-$i);
    return (11-$sum%11)%10===(int)$value[12];
}
function promptpay_tlv(string $id,string $value): string {if(strlen($value)>99)throw new RuntimeException('QR field too long');return $id.str_pad((string)strlen($value),2,'0',STR_PAD_LEFT).$value;}
function promptpay_crc(string $payload): string {$crc=0xFFFF;foreach(str_split($payload) as $char){$crc^=ord($char)<<8;for($i=0;$i<8;$i++)$crc=(($crc&0x8000)?($crc<<1)^0x1021:$crc<<1)&0xFFFF;}return strtoupper(str_pad(dechex($crc),4,'0',STR_PAD_LEFT));}
function promptpay_payload(string $type,string $recipient,int $amount): string {
    if(!promptpay_valid_recipient($type,$recipient)||$amount<1||$amount>99999999999)throw new RuntimeException('ข้อมูล PromptPay หรือยอดชำระไม่ถูกต้อง');
    $account=promptpay_tlv('00','A000000677010111').promptpay_tlv($type==='phone'?'01':'02',$type==='phone'?'0066'.substr($recipient,1):$recipient);
    $value=promptpay_tlv('00','01').promptpay_tlv('01','12').promptpay_tlv('29',$account).promptpay_tlv('53','764').promptpay_tlv('54',intdiv($amount,100).'.'.str_pad((string)($amount%100),2,'0',STR_PAD_LEFT)).promptpay_tlv('58','TH').'6304';
    return $value.promptpay_crc($value);
}
