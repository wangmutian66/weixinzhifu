<?php
require_once 'WechatSdk/WechatPay.php';
require_once 'WechatSdk/transfers.php';
require_once 'WechatSdk/orderinfo.php';

class WeChat {
    //微信授权后，返回的用户信息
    public function getinfo($code) {
       $wechatPay = new WechatPay;
       return $wechatPay->getAccesstokenByCode($code);
    }

    //提现
    public function transfers($appid, $money) {
        $wechatPay = new transfers;
        return $wechatPay->transfers($appid, $money);
    }

    //支付
    public function orderinfo($con, $money) {
    
        $money = (int)$money;

        $wechatPay = new orderinfo;
        return $wechatPay->orderinfo($con,$money);
    }
    

}
    



