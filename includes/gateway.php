<?php
declare(strict_types=1);
interface HostedPaymentGateway {
    /** A real adapter must use hosted/tokenized UI; never accept PAN or CVV here. */
    public function createHostedSession(array $order): array;
    /** Must verify provider signature, timestamp and event authenticity before returning normalized data. */
    public function verifyWebhook(string $body,array $headers): array;
}
function configured_payment_gateways(): array {return []; /* No provider/credentials configured: expose no card/wallet methods. */}
function process_verified_gateway_event(string $provider,array $event): void {
    foreach(['event_id','session_id','amount_cents','currency','paid'] as $key)if(!array_key_exists($key,$event))throw new RuntimeException('Invalid verified event');
    if(!is_string($event['event_id'])||strlen($event['event_id'])>190||$event['event_id']==='')throw new RuntimeException('Invalid event ID');
    $session=q('SELECT * FROM gateway_sessions WHERE provider=? AND external_id=?',[$provider,$event['session_id']])->fetch();if(!$session)throw new RuntimeException('Unknown payment session');
    db()->beginTransaction();
    try{
        $order=locked_order((int)$session['order_id']);$session=q('SELECT * FROM gateway_sessions WHERE id=? FOR UPDATE',[$session['id']])->fetch();
        if(q('SELECT id FROM gateway_events WHERE provider=? AND event_id=?',[$provider,$event['event_id']])->fetchColumn()){db()->rollBack();return;}
        if($event['currency']!=='THB'||!is_int($event['amount_cents'])||$event['amount_cents']!==(int)$session['amount_cents']||$event['amount_cents']!==cents($order['total']))throw new RuntimeException('Payment amount or currency mismatch');
        q('INSERT INTO gateway_events(provider,event_id,order_id) VALUES (?,?,?)',[$provider,$event['event_id'],$order['id']]);
        if($event['paid']===true&&$session['status']!=='paid'){
            if($order['payment_method']!=='GATEWAY'||$order['payment_status']!=='unpaid')throw new RuntimeException('Order cannot accept gateway payment');
            q('UPDATE gateway_sessions SET status="paid" WHERE id=?',[$session['id']]);q('UPDATE orders SET payment_status="paid" WHERE id=?',[$order['id']]);$order['payment_status']='paid';
            // Gateway verified events are system actors, not an arbitrary supplied customer ID.
            q('INSERT INTO order_events(order_id,actor_id,event_type,order_status,payment_status,note) VALUES (?,NULL,"gateway_paid",?,?,?)',[$order['id'],$order['status'],'paid','ยืนยันโดย Webhook ที่ตรวจลายเซ็นแล้ว']);order_notify((int)$order['user_id'],(int)$order['id'],'ผู้ให้บริการยืนยันการชำระเงินแล้ว');
        }
        db()->commit();
    }catch(Throwable $error){if(db()->inTransaction())db()->rollBack();throw $error;}
}
