<?php
function issue_order_document(array $order,array $admin,string $kind,array $buyer): void {
    if($order['payment_status']!=='paid')throw new RuntimeException('ออกใบเสร็จได้หลังยืนยันรับเงินจริงเท่านั้น');
    if(!in_array($kind,['receipt','tax_invoice'],true))throw new RuntimeException('ประเภทเอกสารไม่ถูกต้อง');
    $tax=json_decode($order['tax_snapshot']??'{}',true)?:[];
    if($kind==='tax_invoice'){
        $settings=tax_settings();if(empty($tax['registered'])||empty($tax['invoice_enabled'])||empty($settings['registered'])||empty($settings['invoice_enabled'])||$order['status']==='cancelled')throw new RuntimeException('คำสั่งซื้อนี้ไม่มีสิทธิ์ออกใบกำกับภาษี');
        if(empty($buyer['name'])||empty($buyer['address'])||!preg_match('/^[0-9]{13}$/D',$buyer['tax_id']??''))throw new RuntimeException('ต้องมีชื่อ ที่อยู่ และเลขผู้เสียภาษีจริงของผู้ซื้อครบ');
    }
    foreach($buyer as $value)if(!is_string($value)||mb_strlen($value)>2000)throw new RuntimeException('ข้อมูลผู้ซื้อยาวเกินกำหนด');
    $snapshot=['order'=>$order,'items'=>q('SELECT * FROM order_items WHERE order_id=? ORDER BY id',[$order['id']])->fetchAll(),'tax'=>$tax,'buyer'=>$buyer];
    $number='BP-'.($kind==='receipt'?'R':'T').'-'.date('Ymd').'-'.str_pad((string)$order['id'],6,'0',STR_PAD_LEFT);
    if(q('SELECT id FROM order_documents WHERE order_id=? AND kind=?',[$order['id'],$kind])->fetchColumn())throw new RuntimeException('เอกสารนี้ออกแล้ว ไม่แก้เอกสารเดิม');
    q('INSERT INTO order_documents(order_id,kind,number,snapshot,issued_by) VALUES (?,?,?,?,?)',[$order['id'],$kind,$number,json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$admin['id']]);
}
