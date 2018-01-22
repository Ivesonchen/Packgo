<?php
// ========================================================
// =                       wechat.php                     =
// =                         微信类                        =
// =                                                      =
// =   @atuhor IthilQurssir                               =
// =   @email  itimecracker@gmail.com                     =
// =   @time   2015-08-10                                 =
// ========================================================





//access_token有效期 S
define('EFFECT_TIME',6666);
//微信目录
define('WEIXIN_ROOT', dirname(__FILE__) . '/' );
//微信管理员邮箱
define('WEIXIN_ADMIN_EMAIL','itimecracker@gmail.com');


/** 微信接口类
 * 
 * @author IthilQuessir
 * @email itimecracker@gmail.com
 */
class WeChat{
	
	private $appID				= '';			// 微信appID
	private $appsecret			= '';			// 微信appsecret
	
	const ACCESS_FILE	= '../tmp/wechat.access_token.tmp';		// access_token缓存文件URL
	const JSAPI_FILE	= '../tmp/wechat.jsapi_ticket.tmp';		// jsapi_ticket缓存文件URL
	const ERROR_LOG		= '../log/wechat.error.log';			// 错误日志
	
	const SSL 			= '/home/wwwroot/wechat_pay';			// 证书文件地址
	
	/** WeChat类
	 * 
	 * @param	$appid		string	微信公共号appID
	 * @param	$apps		string	微信号appsecret
	 * @param	$file_url	string	catch文件url
	 */
	public function __construct( $appid , $apps ){
		$this->appID = $appid;
		$this->appsecret = $apps;
	}
	
	
	/** 获取微信access_token
	 * 
	 * @return access_token未过期则返回access_token票据，否则返回false.
	 */
	public function GetAccessToken(){
		$result = $this->GetCacheInfo( WEIXIN_ROOT . self::ACCESS_FILE );
		
		return $result ? $result : $this->updateAccessToken();
	}
	
	/** 获取jsapi_ticket
	 * 
	 * @return access_token未过期则返回access_token票据，否则返回false.
	 */
	public function GetJsapiTicket()
	{
		$result = $this->GetCacheInfo( WEIXIN_ROOT . self::JSAPI_FILE );
		
		return $result ? $result : $this->updateJsapiTicket();
	}
	
	/** 获取网页签名signature
	 * 
	 * @param	$url	string	生成signature的网址
	 *
	 * @return	正常获取signature则返回$arr	$arr存储
	 *			否则返回false
	 */
	public function GetWebSignature( $url )
	{
		$jsapi_ticket = $this->GetJsapiTicket();
		
		if( $jsapi_ticket )
		{
			$arr = array( 'noncestr' => $this->GetRandString( 16 ) , 'timestamp' => time() , 'signature' => '');
		
			$sha1 = 'jsapi_ticket=' . $jsapi_ticket . '&noncestr=' . $arr['noncestr'] . '&timestamp=' . $arr['timestamp'] . '&url=' . $url;

			$arr['signature'] = sha1($sha1);
			
			return $arr;
		}
		
		return false;
	}
	
	
	/** 刷新微信票据 access_token
	 *
	 * @return 成功获取并写入返回access_token 否则返回false
	 */
	private function updateAccessToken()
	{
		//微信获取access_token接口
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appID . '&secret=' . $this->appsecret;
		$json = file_get_contents($url);
		$arr = json_decode($json, true);
		
		if( $this->Json_Error($arr,'UpdataAccessToken') )
			return false;
		
		return file_put_contents( WEIXIN_ROOT . self::ACCESS_FILE , ( time() + EFFECT_TIME ) . "\n" .  $arr['access_token'] ) ? $arr['access_token'] : false;
	}

	
	/** 刷新微信票据 jsapi_ticket
	 *
	 * @return 成功获取返回jsapi_ticket 否则返回false
	 */
	private function updateJsapiTicket()
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $this->GetAccessToken() . '&type=jsapi';
		$json = file_get_contents($url);
		$arr = json_decode( $json , true );
		if( $this->Json_Error( $arr , 'UpdataJsapiTicket') )
			return false;
		
		return file_put_contents( WEIXIN_ROOT . self::JSAPI_FILE , ( time() + EFFECT_TIME ) . "\n" .  $arr['ticket'] ) ? $arr['ticket'] : false;
	}
	
	
	/** 从缓存中获取access_token/jsapi_ticket
	 * 
	 * @return 缓存未过期返回票据  否则返回false
	 */
	private function GetCacheInfo( $url )
	{
		if( file_exists( $url ) )
		{
			//记录文件存在
			//获取文件信息 并确定是否过期
			try{
				$arr = file( $url );
			}catch(Exception $e){
				return false;
			}
			
			if( ( (int) $arr[0] ) < time() )
				return false;		// 过期
			else
				return $arr[1];		// 未过期
				
		}
		else
			return false;			//access_token记录文件不存在
	}
	
	
	/** 获取用户openid
	 * 
	 * @param $code 用户同意授权后获取的时效5分钟验证码
	 *
	 * @return 获取成功返回openid，否则返回false
	 */
	public function GetUserOpenID( $code )
	{
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appID . '&secret=' . $this->appsecret .'&code=' . $code . '&grant_type=authorization_code';
		
		$json = json_decode(file_get_contents($url),true);
		
		if( $this->Json_Error( $json , 'GetUserOpenId' ) )
			return false;
		
		return $json["openid"];
	}
	
	/** 获取关注用户的openid列表
	 * 
	 * @param $next_openid	关注用户列表偏移量，不填默认从头开始拉取
	 * 
	 * @return Array	用户openid列表
	 */
	public function GetUserList( $next_openid = '' )
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->GetAccessToken() . "&next_openid=" . $next_openid;
		
		$json = json_decode(file_get_contents($url),true);
		
		return $json;
	}
	
	/** 获取某用户信息
	 * 
	 * @param 
	 */
	public function GetUserInfo( $openid )
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->GetAccessToken() . "&openid=" . $openid;
		return json_decode(file_get_contents($url),true);
	}
	
	/** 下载多媒体文件
	 * 
	 * @param String $media_id 多媒体资源的id
	 * @param String $file_url 获取成功后文件的存储地址  实际存储地址为 `$file_url + 请求后返回的filename`
	 * 
	 * @return 如果成功则返回存储地址，否则返回false
	 */
	public function GetMedia( $media_id , $file_url )
	{
		$url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->GetAccessToken() . '&media_id=' . $media_id;
		$str = file_get_contents( $url );
		
		// 尝试获取头信息的filename
		$filename = stristr($http_response_header[3],'filename');
		
		//如果filename不存在，则获取多媒体失败
		if($filename)
		{
			//判断路径是否存在
			if( !file_exists($file_url) )
			{
				mkdir( $file_url , 0755 , true );
			}
			
			// 组成新路径并存储多媒体文件
			$filename = explode( "\"" , $filename )[1];
			$file_url = $file_url . $filename;
			
			file_put_contents( $file_url , $str );
			
			return $filename;
		}
		else
		{
			$this->Json_Error( json_decode($str,true) , 'GetMedia' );
			return false;
		}
	}
	
	/** 发送模板消息
	 * 
	 * @param $info array 发送的消息
	 */
	public function SendModeInfo($info)
	{
		return $this->post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->GetAccessToken() , urldecode(json_encode($info)));
	}
	
	
	/** 获取微信预支付交易单
	 * 
	 */
	public function GetUnifiedOrder( $openid , $orderinfo , $orderid , $totalfee )
	{
		// echo $openid . "<hr/>";
		$order_list = array(
			"appid"				=> $this->appID,
			"body"				=> "袋袋GO订单",							// 商品描述
			"detail"			=> $orderinfo,						// 商品详情
			"mch_id"			=> "1268215201",						// 商户号
			"nonce_str"			=> $this->GetRandString(24),			// 随机字符串
			"notify_url"		=> "http://hihigh.net/api/order/receiveWechatPay.php",						// 接收微信支付异步通知回调地址
			"openid"			=> $openid,								// 用户openid
			"out_trade_no"		=> $orderid,		// 订单号
			"spbill_create_ip"	=> $reIP=$_SERVER["REMOTE_ADDR"],		// 客户端IP
			"total_fee"			=> $totalfee,
			"trade_type"		=> "JSAPI",
			"sign"				=> '',
		);
		// 生成签名
		$signature = $this->GetOrderSignature($order_list) . "key=wangzengguang1583316801119960203"; 
		
		
		// 设置签名
		$order_list["sign"] = strtoupper( MD5($signature) );
		// 生成xml
		$xml = $this->getXmlByArray( array("xml"=>$order_list) );
		
		$xml = $this->post('https://api.mch.weixin.qq.com/pay/unifiedorder',$xml);
		
		// echo "console.log($xml);";
		$p = xml_parser_create();
		xml_parse_into_struct($p, $xml, $vals, $index);
		xml_parser_free($p);
		
		if( $vals[$index["RETURN_CODE"][0]]["value"] == 'FAIL')
		    return $vals[$index["RETURN_MSG"][0]]["value"];
// 			throw new Exception("获取预支付交易单信息失败" . $vals[$index["RETURN_MSG"][0]]["value"] );
		
		// $arr = array(
		// 	"return_code"	=> $vals[$index["RETURN_CODE"][0]]["value"],
		// 	"return_msg"	=> $vals[$index["RETURN_MSG"][0]]["value"],
		// 	"appid"			=> $vals[$index["APPID"][0]]["value"],
		// 	"mch_id"		=> $vals[$index["MCH_ID"][0]]["value"],
		// 	"nonce_str"		=> $vals[$index["NONCE_STR"][0]]["value"],
		// 	"sign"			=> $vals[$index["SIGN"][0]]["value"],
		// 	"result_code"	=> $vals[$index["RESULT_CODE"][0]]["value"],
		// 	"prepay_id"		=> $vals[$index["PREPAY_ID"][0]]["value"],
		// 	"trade_type"	=> $vals[$index["TRADE_TYPE"][0]]["value"]
		// );
		
		$sign = array(
			"appId"		=> $this->appID,
			"timeStamp"	=> time(),
			"nonceStr"	=> $this->GetRandString(24),
			"package"	=> "prepay_id=" . $vals[$index["PREPAY_ID"][0]]["value"],
			"signType"	=> "MD5",
			"paySign"	=> null
		);
		
		$sign["paySign"] =strtoupper(MD5( $this-> GetOrderSignature($sign) . "key=wangzengguang1583316801119960203" ));
		
		$sign['xml'] = htmlspecialchars($vals);
		
		return $sign;
	}
	
	/** 退款
	 */
	public function OrderRefund( $order_code , $refund_code , $refund_fee , $total_fee )
	{
		$order_list = array(
			"appid"				=> $this->appID,
			"mch_id"			=> "1268215201",				// 商户号
			"nonce_str"			=> $this->GetRandString(24),	// 随机字符串
			// "transaction_id"	=> $order_code,					// 微信单号
			"out_trade_no"		=> $order_code,					// 商户单号
			"out_refund_no"		=> $refund_code,				// 商户退款单号
			"total_fee"			=> $total_fee,					// 总金额
			"refund_fee"		=> $refund_fee,					// 退款金额
			"op_user_id"		=> "在下",						// 操作员
			"sign"				=> ''
		);
		// echo "生成签名<hr/>";
		// 生成签名
		$signature = $this->GetOrderSignature($order_list) . "key=wangzengguang1583316801119960203";
		// 设置签名
		$order_list["sign"] = strtoupper( MD5($signature) );
		// 生成XML
		$xml = $this->getXmlByArray( array("xml"=>$order_list) );
		
		// echo "XML:<br/>";
		//echo htmlspecialchars($xml);
		// echo "<hr/>";
		
		// echo "发送请求<hr/>";
		// 发送请求
		$xml = $this->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', $xml);
		// echo "请求发送完成<hr/>";
		return $xml;
	}
	
	
	/** 微信订单签名生成
	 * @discription 空字段不参与生成
	 * 
	 * @param $sign Array 生成签名的数组
	 * 
	 * @return 生成的字符串
	 */
	private function GetOrderSignature($sign){
		ksort($sign);
		
		// 生成签名
		$signature = '';
		foreach ($sign as $key => $val) {
			if(!empty($val))
			{
				$signature .= "$key=$val&";
			}
		}
		return $signature;
	}
	
	
	
	/** 生成XML
	 *
	 * @param $arr Array 用于生成xml的数组
	 * 
	 * @return XML字符串
	 *
	 * @description 递归
	 */
	private function getXmlByArray($arr)
	{
		$xml = '';
		foreach ($arr as $key => $val) {
		
			if( is_array($val)  )
			{
				$element = $this->getXmlByArray( $val );
				
				$xml .= "<$key>" . $element . "</$key>";
			}
			else{
				$xml .= "<$key>" . $val . "</$key>";
			}
		}
	
		return $xml;
	}
	
	
	
	/** 检测返回JSON信息是否有错误
	 *
	 * @param	$arr	array	json信息
	 * @param  $errch	string	当前操作
	 *
	 * @return JSON错误返回true,否则返回false.
	 * @error格式       appID:错误码:错误信息:时间:操作
	 */
	private function Json_Error( $arr , $errch )
	{
		if( array_key_exists( 'errcode' , $arr ) && $arr['errcode'] )
		{
			try{
				$file_err = file_put_contents( WEIXIN_ROOT . self::ERROR_LOG , $this->appID . ':' . $arr['errcode'] . ':' . $arr['errmsg'] . ':' . date("Y-m-d  H:i:s") . ':' . $errch . "\r\n" , FILE_APPEND ) ? '错误正常记录' : '无法记录该错误';
			
				// 发送错误信息到管理员邮箱
				$err = "微信错误：" . $arr['errcode'] ."\t" . $arr['errmsg'] . "\r\n错误记录：" . $file_err . "\r\n错误时间：" . date("Y-m-d  H:i:s") . "\r\n引起错误的操作：" . $errch;
				error_log( $err , 1 , WEIXIN_ADMIN_EMAIL , "From: localhost@onewline.com" );
			}catch(Exception $e){};
			
			return true;
		}
		
		return false;
	}
	
	
	/** 获取随机字符串
	 *
	 * @param	$length int 字符串长度
	 *
	 * @return	string 随机字符串
	 */
	private function GetRandString( $length )
	{
		// 密码字符集，可任意添加你需要的字符  
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
		$password = '';
		
		for ( $i = 0; $i < $length; $i++ )  
		{  
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}
		return $password;
	}
	
	
	
	
	/** POST/Get发送数据
	 *
	 * @param $url			string	发送数据的目标url
	 * @param $post_data	array	psot的数据,若空则为GET
	 * @param $timeout		int		等待时间
	 *
	 * @return	psot的返回值
	 */
	private function post($url, $post_data = null, $timeout = 5){
		$ch = curl_init();
		
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HEADER, false);
		
		if(!empty($post_data))
		{
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		
		$file_contents = curl_exec($ch);
		
		curl_close($ch);
		
		return $file_contents;
	}
	
	
	
	
	
	public function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
	
		//以下两种方式需选择一种
		
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, self::SSL .'/packgo_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, self::SSL .'/packgo_key.pem');
	
		//第二种方式，两个文件合成一个.pem文件
		// curl_setopt($ch,CURLOPT_SSLCERT, self::SSL . '/all.pem');
 	   
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
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n"; 
			curl_close($ch);
			return false;
		}
	}

	//
	// $data = curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', 'merchantid=1001000');
	// print_r($data);
}
?>