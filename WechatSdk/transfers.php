<?php
	/**
	 * 微信转账
	 * 转账至openid
	 */
	class Transfers{
		public function __construct($conf = array()){
			// 引入配置文件
			require_once 'config.php';

			// 载入相关函数
			require_once 'WechatTools.php';
		}

		public function transfers($appid, $money) {
			$arr = array();
			//$arr['mch_appid'] = 'wxe61dd0756cd97a16';	// 公众号ID
			$arr['mch_appid'] = 'wx3d2694e80cfda3fa';	// 公众号ID
			$arr['mchid'] = '1480633332';	
			//$arr['device_info'] = "WEB";
			$arr['nonce_str'] = WechatTools::getNonceStr(32);
			$arr['partner_trade_no'] = time() . rand(1000,9999);
			$arr['openid'] = $appid;	// openid
			$arr['check_name'] = "NO_CHECK"; 	// 校验姓名  不校验NO_CHECK  校验FORCE_CHECK
			$arr['re_user_name'] = "用户";	// 收款人姓名
			//$arr['amount'] = 100;	// 	企业付款金额，单位为分  单笔不小于1元
			$arr['amount'] = $money;	// 	企业付款金额，单位为分  单笔不小于1元
			$arr['desc'] = "提现";	//	企业付款操作说明信息。必填。
			//$arr['spbill_create_ip'] = "139.129.233.137";	// 调用接口的机器Ip地址 getIp
			$arr['spbill_create_ip'] = "192.168.0.1";
			
			$arr['sign'] = WechatTools::getSign($arr);
			$xml = WechatTools::arrayToXml($arr);

			$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
			
			$content = WechatTools::curl_post_ssl($url,$xml);
			file_put_contents('./wang2222.txt',$content);
			$content = WechatTools::xmlToObject($content);
			
			if(empty($content)) exit();

			if( "FAIL" == $content->return_code ){
				// 通信失败   即转账失败
				// echo $content->return_msg;
				return $content->return_msg;
			}else{
				if( "SUCCESS" == $content->result_code ){
					// 转账成功
					$data['partner_trade_no'] = $content->partner_trade_no;	// 商户订单号
					$data['payment_no'] = $content->payment_no;	// 微信订单号
					$data['payment_time'] = $content->payment_time;	// 交易成功时间
					// echo "<pre>";
					// print_r($content);
					return $data;
					/**
					 * 逻辑处理
					 */

				}else{
					// echo "错误代码：".$content->err_code . ";<br>错误描述：".$content->err_code_des;
					return $content;
				}
			}
		}


	}

?>