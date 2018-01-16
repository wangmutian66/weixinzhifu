<?php


include 'WeChat.class.php';

$WeChat = new WeChat();

$orderinfo = $WeChat->orderinfo('ABC', 100,"lhl_123_231");
//var_dump($orderinfo);
//exit();
echo json_encode($orderinfo);
exit();
/**
 * $data['body'] = $con;
$data['out_trade_no'] = time() . rand(1000,9999);
//$data['total_fee'] = 98;
$data['total_fee'] = $money;
$data['spbill_create_ip'] = '192.168.1.1';
$data['attach'] = "怪掌门";
$data['device_info'] = "WEB";
$data['notify_url'] = "";
 */



include_once 'wxapi/lib/WxPay.Api.php';
include_once 'wxapi/lib/WxPay.Data.php';


$WxPayUnifiedOrder=new WxPayUnifiedOrder();
//$WxPayUnifiedOrder->SetAppid("wx900520eae73ea184");
$WxPayUnifiedOrder->values["appid"]="wx900520eae73ea184";
$WxPayUnifiedOrder->values["out_trade_no"]=time() . rand(1000,9999);
$WxPayUnifiedOrder->values["body"]="ceshiwan";
$WxPayUnifiedOrder->values["total_fee"]=98;
$WxPayUnifiedOrder->values["spbill_create_ip"]='192.168.1.1';
$WxPayUnifiedOrder->values["attach"]="怪掌门";
$WxPayUnifiedOrder->values["device_info"]='WEB';
$WxPayUnifiedOrder->values["notify_url"]='http://www.guaizhangmen.com/author.php/Nexts/webCallback';
$WxPayUnifiedOrder->values["trade_type"]='APP';
//$WxPayUnifiedOrder->IsOut_trade_noSet() = time() . rand(1000,9999);
$array= WxPayApi::unifiedOrder($WxPayUnifiedOrder);

var_dump($array);
exit();

