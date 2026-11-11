<?php
declare(strict_types=1);
function tax_settings(): array {return q('SELECT * FROM tax_settings WHERE id=1')->fetch()?:[];}
function tax_quote(array $quote,array $rows,string $method): array {
    $settings=tax_settings();$registered=!empty($settings['registered'])&&!empty($settings['confirmed_by'])&&!empty($settings['confirmed_at']);
    $fee=$method==='COD'?cents($quote['service']['cod_fee']??0):0;$raw=$quote['total']+$fee;$net=$raw;$vat=0;$rate=0;
    if($registered){
        $rates=q('SELECT * FROM tax_rates WHERE active=1 AND starts_on<=? AND ends_on>=? ORDER BY starts_on DESC',[date('Y-m-d'),date('Y-m-d')])->fetchAll();
        if(count($rates)!==1)throw new RuntimeException('กรุณาให้ร้านตั้งอัตราภาษีที่มีผลวันนี้ให้ชัดเจน');$rate=(int)$rates[0]['rate_bps'];
        $amounts=[];foreach($rows as $row){$key=$row['cart_key']??(int)$row['id'];if(empty($row['tax_exempt']))$amounts[]=$quote['line_totals'][$key];}
        if($settings['shipping_taxable'])$amounts[]=$quote['shipping']+$fee;
        foreach($amounts as $amount){
            if($settings['price_mode']==='inclusive'){$base=intdiv($amount*10000+intdiv(10000+$rate,2),10000+$rate);$vat+=$amount-$base;}
            else $vat+=intdiv($amount*$rate+5000,10000);
        }
        if($settings['price_mode']==='inclusive')$net=$raw-$vat;else{$net=$raw;$raw+=$vat;}
    }
    $quote['cod_fee']=$fee;$quote['net_before_tax']=$net;$quote['vat_amount']=$vat;$quote['total']=$raw;
    $quote['tax_registered']=$registered;$quote['tax_rate_bps']=$rate;
    $quote['tax_snapshot']=['registered'=>$registered,'rate_bps'=>$rate,'price_mode'=>$settings['price_mode']??'inclusive','shipping_taxable'=>(bool)($settings['shipping_taxable']??false),'tax_id'=>$registered?$settings['tax_id']:'','legal_name'=>$registered?$settings['legal_name']:'','legal_address'=>$registered?$settings['legal_address']:'','branch'=>$registered?$settings['branch']:'','invoice_enabled'=>$registered&&!empty($settings['invoice_enabled'])];
    return $quote;
}
