<?php
/**
 * 微信支付相关类
 * 目前适用于APP
 */

class WechatPay{

	private $conf;			// 全局配置参数  此配置优先于config.php文件配置
	private $debug = false;	// 是否开启调试模式	开启调试模式只对curl操作有效

	/**
	 * [构造方法	创建实例配置参数  默认使用config参数]
	 * @param [array] $conf [参数  包含 trade_type  application_id  mchid]
	 */
	public function __construct($conf = array()){
		if(is_array($conf)){
			$this->conf = $conf;
		}

		// 引入配置文件
		require_once 'config.php';

		// 载入相关函数
		require_once 'WechatTools.php';
	}

	/**
	 * [setConf 参数配置]
	 * @param array $conf [参数  包含 trade_type  application_id  mchid]
	 */
	public function setConf($conf = array()){
		if(is_array($conf))	$this->conf = $conf;	// 参数配置
		return $this;
	}

	/**
	 * [sendRequestApp App 下单请求]
	 * @param  [array] $data [订单数据]
	 * @return []       [成功返回签名  失败返回false]
	 */
	public function sendRequestApp($data = array()) {
		$out_trade_no = $data['out_trade_no'];
	    $data = $this->setSendDataApp($data);

	    $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

	    $res = WechatTools::curl($url, $data , "POST");

	    if($res['res']){
	    	// curl成功

	    	$xml = WechatTools::xmlToObject($res['content']);            //解析返回数据

			if ($xml === false) {
				if($this->debug){
					$this->__error("<pre>微信统一下单返回xml数据解析失败 xml:".$xml."</pre>");
				}else{
					return false;
				}
			}

			if ($xml->return_code == 'FAIL') {
				if($this->debug){
					$this->__error("微信返回错误码为FAIL，请求失败，返回失败信息:".$xml->return_msg);
				}else{
					return false;
				}
			} else {
				

			    $data = array();
			    $data['appid'] = $xml->appid;
				$data['noncestr'] = WechatTools::getNonceStr();
				$data['package'] = "Sign=WXPay";
				$data['partnerid'] = $xml->mch_id;
				$data['prepayid'] = $xml->prepay_id;
				$data['timestamp'] = time();
				$data['sign'] = WechatTools::getSign($data);
				$data['out_trade_no'] = $out_trade_no;



			    return $data;
			}

	    }elseif($this->debug){
	    	$this->error("Curl error: " . $res['error']);
	    }else{
	    	return false;
	    }
	}

	/**
	 * [notifyVerify 异步通知回调处理  可开]
	 * @return 
	 */
	public function notifyVerify(){
		$obj = WechatTools::getXmlData();
		
		if(empty($obj))	exit();	// 判断乱入

		if ($obj->return_code == 'FAIL') {
			/**
			 * 此处做 通信失败 的处理流程
			 */
			exit();
		}

		$data = array(
			'appid'				=>	$obj->appid,
			'mch_id'			=>	$obj->mch_id,
			'nonce_str'			=>	$obj->nonce_str,
			'result_code'		=>	$obj->result_code,
			'openid'			=>	$obj->openid,
			'trade_type'		=>	$obj->trade_type,
			'bank_type'			=>	$obj->bank_type,
			'total_fee'			=>	$obj->total_fee,
			'cash_fee'			=>	$obj->cash_fee,
			'transaction_id'	=>	$obj->transaction_id,
			'out_trade_no'		=>	$obj->out_trade_no,
			'time_end'			=>	$obj->time_end
			);

		$sign = WechatTools::getSign($data);

		if ($sign == $obj->sign) {
			// 成功 回信息给微信
			$reply = "<xml>
						<return_code><![CDATA[SUCCESS]]></return_code>
						<return_msg><![CDATA[OK]]></return_msg>
					</xml>";
			echo $reply;
			/**
			 * 具体怎么返回按需求定
			 */
			return $data;
		}else{
			/**
			 * 此处做校验失败处理  可回调 call_user_fun
			 */
		}

	}

	/**
	 * [orderQuery 查询订单 可开]
	 * @param array $content 查询数据  必要:orderid  非必要:appid,mch_id
	 * @return []  $res    返回结果 判断 $res === false 时为查询失败   is(array($res)) 返回[orderid:订单号 string]及[payresult:支付结果 bool true成功] 
	 */
	public function orderQuery($content){
	

		if(!$content['out_trade_no'])	return false;

		$content['appid'] == "" && $content['appid'] = ( $this->conf['application_id'] ? $this->conf['application_id'] : APPLICATION_ID );
		$content['mch_id'] == "" && ( $content['mch_id'] = $this->conf['mchid'] ? $this->conf['mchid'] : MCHID );
		$content['nonce_str'] = WechatTools::getNonceStr();
		$act =$content['orderid'];
		unset($content['orderid']);
		
		$sign = WechatTools::getSign($content);
		$xml_data = '<xml>
		   <appid>%s</appid>
		   <mch_id>%s</mch_id>
		   <nonce_str>%s</nonce_str>
		   <out_trade_no>%s</out_trade_no>
		   <sign>%s</sign>
		</xml>';
		$content['orderid'] = $act;
		$xml_data = sprintf($xml_data, $content['appid'], $content['mch_id'], $content['nonce_str'], $content['orderid'] , $sign);
		
		$result = WechatTools::curl("https://api.mch.weixin.qq.com/pay/orderquery",$xml_data,"POST");
		
		
		if(!$result['res']){
			// curl失败
			if($this->debug){
				$this->__error("Curl error: " . $res['error']);
			}else{
				return false;
			}
		}

		$xml = WechatTools::xmlToObject($result['content']);			//解析返回数据
		/** 
		 * 判断通讯是否成功
		 */
		if('FAIL' == $xml->return_code){
			if($this->debug){
				$this->__error("微信返回错误码为FAIL，请求失败，返回失败信息:".$xml->return_msg);
			}else{
				return false;
			}
		}else{
			if( 'SUCCESS' == $xml->result_code ){
				// 支付成功
				return array("orderid"=>$content['orderid'],"payresult"=>true,'xml'=>$xml);
			}else{
				// 支付失败
				return array("orderid"=>$content['orderid'],"payresult"=>false);
			}
		}
	}

	public function getAccesstokenByCode($code = ''){
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPLICATION_ID.'&secret='.APPLICATION_SECRET.'&code='.$code.'&grant_type=authorization_code';

		$res = WechatTools::curl($url);
		if( !$res['res'] ){
			return false;
		}

		$content = json_decode($res['content'],true);

		if(!$content['access_token']){
			if($this->debug){
				$this->__error("通过CODE获取access_token失败");
			}else{
				return false;
			}
		}

		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$content['access_token']."&openid=".$content['openid']."&lang=zh_CN";
		$res = WechatTools::curl($url);
        file_put_contents("./Public/content11.txt",$res["content"]);
		if(!$res){
			return false;
		}else{
			return json_decode($res['content'],true);
		}
	}

	/**
	 * [setSendDataApp 组装xml 使用APP模式]
	 * @param array $data [订单数据]
	 * @return [string]   [xml订单数据]
	 */
	private function setSendDataApp($data = array()) {
		$this->checkData($data, "APP");

	    $data['sign'] = WechatTools::getSign($data);        //获取签名

	    ksort($data);
	    return WechatTools::arrayToXml($data);
	}

	private function checkData(&$data=array(), $tradeType = "APP"){
		if($tradeType == "APP"){

	    	// 注意：$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
		    $data['appid'] == "" && $data['appid'] = $this->conf['application_id'] ? $this->conf['application_id'] : APPLICATION_ID;	// 微信开放平台审核通过的应用APPID
		    $data['mch_id'] == "" && $data['mch_id'] = $this->conf['mchid'] ? $this->conf['mchid'] : MCHID;	// 微信支付分配的商户号
		    $data['nonce_str'] == "" && $data['nonce_str'] = WechatTools::getNonceStr(); //调用随机字符串生成方法获取随机字符串
		    $data['notify_url'] == "" && $data['notify_url'] = $this->conf['notify_url'] ? $this->conf['notify_url'] : DEFAULT_NOTIFY_URL;
		    $data['trade_type'] == "" && $data['trade_type'] = $this->conf['trade_type'] ? $this->conf['trade_type'] : DEFAULT_TRADE_TYPE;

		  
	        if($data['body'] == "") $this->__error("缺少参数 \"body\"; 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值。");
	        if($data['out_trade_no'] == "") $this->__error("缺少参数 \"out_trade_no\"; 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*@ ，且在同一个商户号下唯一");
	        if(!is_int($data['total_fee']) || $data['total_fee'] === 0) $this->__error("参数 \"total_fee\" 需为整数且大于0，单位 分");
	        if(!$data['spbill_create_ip']) $this->error("缺少参数 \"spbill_create_ip\"; 用户端实际ip");

		}
	}

	/**
	 * [__error 异常处理]
	 * @param  string $errmsg [错误信息]
	 * @return nil
	 */
	private function __error($errmsg = ""){
		throw new Exception($errmsg);
		exit();
	}

}