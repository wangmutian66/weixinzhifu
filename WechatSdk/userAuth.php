<?php
	/**
	 * 用户鉴权登陆
	 * 按需修改
	 */
	require "WechatPay.php";
	$wechatPay = new WechatPay;
	$wechatPay->getAccesstokenByCode();

    
?>