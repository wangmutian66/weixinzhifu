<?php
	/**
	 * 微信订单查询
	 */
	require "WechatPay.php";
	$wechatPay = new WechatPay();
	$wechatPay->orderQuery("订单号");
?>