<?php
/**
 * 微信工具类
 */
	class WechatTools{

		/**
		 * 获取参数签名；
		 * @param  Array  要传递的参数数组
		 * @return String 通过计算得到的签名；
		 */
		public static function getSign($params) {
	
			foreach ($params as $k => $v)
		    {
		        $params[strtolower($k)] = $v;
		    }
		    ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
		    foreach ($params as $key => $item) {
		        if (!empty($item)) {         //剔除参数值为空的参数
		            $newArr[] = $key.'='.$item;     // 整合新的参数数组
		        }
		    }

		    $stringA = implode("&", $newArr);         //使用 & 符号连接参数

		    $stringA = $stringA."&key=".SHOP_API_KEY;        //末尾拼接key

		    return strtoupper(md5($stringA)); //将字符串进行MD5加密  转大写
		    
		}

		/**
		 * [getNonceStr 随机字符串]
		 * @param  integer $length 随机长度 默认10
		 * @return string
		 */
		public static function getNonceStr($length = 32) {
		    $str = null;
		    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		    $max = strlen($strPol)-1;

		    for($i=0;$i<$length;$i++){
		        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		    }

		    return $str;
		}

		public static function curl($url = "", $data = array(), $requestType = "GET", $useCert = false, $isHttps = true){
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			if ($isHttps) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			}

	        if($useCert){
	            //设置证书
	            //使用证书：cert 与 key 分别属于两个.pem文件
	            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
	            
	            curl_setopt($ch,CURLOPT_SSLCERT, SSLCERT_PATH);
	            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
	            curl_setopt($ch,CURLOPT_SSLKEY, SSLKEY_PATH);
	        }

 			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

			if (!empty($data)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}

			if ($requestType == 'POST') {
				curl_setopt($ch, CURLOPT_POST, true);
			}

			$content = curl_exec($ch);
			
			curl_close($ch);
			if (!empty($content)) {
				return array("res"=>true,"content"=>$content);
	        } else {
	            return array("res"=>false,"error"=>curl_error($ch));
	        }
		}
		public static function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
		{
			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_TIMEOUT,$second);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
			
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT,self::getRealPath().SSLCERT_PATH);
			curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '1480633332'); 
							 
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY,self::getRealPath().SSLKEY_PATH);
			
			curl_setopt($ch,CURLOPT_CAINFO,'PEM');
			curl_setopt($ch,CURLOPT_CAINFO,self::getRealPath().SSLROOTCA);
			if( count($aHeader) >= 1 ){
				curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			}
		 
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
			$data = curl_exec($ch);
			if($data){
				
				curl_close($ch);
				return $data;
				
			}
			else { 
				
				$error = curl_error($ch);
				echo "<pre>";
				print_r($error);
				$error = curl_errno($ch);
				echo "call faild, errorCode:$error\n"; 
				curl_close($ch);
				return false;
			}
		}
		
		public static function getRealPath(){
			return __DIR__.'/';
		}
		/**
		 * [xmlToObject 解析xml数据]
		 * @param  [string] $xmlStr [xml数据]
		 * @return 
		 */
		public static function xmlToObject($xmlStr = ""){

			if (!is_string($xmlStr) || empty($xmlStr)) {
				return false;
			}

			$xml = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$xml = json_decode(json_encode($xml));
			return $xml;
		}

		/**
		 * [getXmlData 获取post xml数据]
		 * @return [type] [description]
		 */
		public static function getXmlData(){
			$postXml = $GLOBALS["HTTP_RAW_POST_DATA"];	//接受通知参数；

			if (empty($postXml)) {
				return false;
			}
			$postObj = self::xmlToObject($postXml);

			if ($postObj === false) {
				return false;
			}

			return $postObj;
		}

		public static function arrayToXml($arr = array()){
			
			$xml = "<xml>";
		    foreach ($arr as $key=>$val)
		    {
		        if (is_numeric($val))
		        {
		            $xml.="<".$key.">".$val."</".$key.">";

		        }
		        else
		            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
		    }
		    $xml.="</xml>";
		    return $xml;
		}
	}
?>