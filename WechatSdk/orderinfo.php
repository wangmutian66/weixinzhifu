<?php
	/**
	 * 微信生成订单
	 */
	class orderinfo{
		public function __construct($conf = array()){
			// 载入相关函数
			require_once 'WechatTools.php';
		}

		public function orderinfo($con,$money) {

			$wechatPay = new WechatPay();
			$data['body'] = $con;
			$data['out_trade_no'] = time() . rand(1000,9999);
			//$data['total_fee'] = 98;
			$data['total_fee'] = $money;
			$data['spbill_create_ip'] = '192.168.1.1';
			$data['attach'] = "怪掌门";
			$data['device_info'] = "WEB";
			$data['notify_url'] = "";
			
			$res = $wechatPay->sendRequestApp($data);
			
			if($res !== false) return $res;
		}
	}

	
