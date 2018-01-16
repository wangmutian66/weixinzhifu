<?php
	ini_set("display_errors", 0);
	ini_set("error_reporting",0);
	// 微信支付参数
	define('SHOP_API_KEY','ldbt1166ldbt1166ldbt1166ldbt1166');	//  商户平台->账户设置->Api安全->密钥 key的值长度不能超过32位
	define('APPLICATION_ID','wx900520eae73ea184');	// 微信开放平台审核通过的应用APPID  345b495d56b0ed26c39eae198750c4b5
	define('APPLICATION_SECRET','1cb5090bd80b742d598e5368f11d033e');
	define('DEFAULT_NOTIFY_URL','http://www.guaizhangmen.com/author.php/Nexts/webCallback');	// 异步通知回调地址
	define('DEFAULT_TRADE_TYPE','APP');	// 默认交易类型
	define('MCHID', '1492764802');	// 微信支付分配的商户号


	/** 微信退款证书 */
	
// 	define('SSLCERT_PATH', './cert/apiclient_cert.pem');
// 	define('SSLKEY_PATH', './cert/apiclient_key.pem');
// 	define('SSLROOTCA', './cert/rootca.pem');
	
?>