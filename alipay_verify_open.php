<?php
	/* 
	* @author John
	* date 2014-4-25
	* 支付宝公众服务平台  开发者模式验证开启
	*/
$alipay = new alipay();
$alipay->init();
class alipay{
	private $rsaPrivateKeyFilePath = './aop/key/rsa_private_key.pem'; //私钥文件地址
	private $rsaPublicKeyFilePath = './aop/key/rsa_public_key.pem';   //公钥文件地址
	private $sign_type = 'RSA';  //签名类型
	private $success = false;

	public function init(){
		$postStr = $_REQUEST["biz_content"];  //$GLOBALS["HTTP_RAW_POST_DATA"];
		$data = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);  
		if(!empty($data)){
			if($data->EventType == verifygw){ //验证支付宝网关校验请求
				$this->success = true;
				$sign = $this->sign($this->publickey);
				
			}
		}
		$this->response($sign);
	}
	//加签 
	private function sign($data) {
		$priKey = file_get_contents($this->rsaPrivateKeyFilePath);
		$res = openssl_get_privatekey($priKey);  //这里是开发者的私钥
		openssl_sign($data, $sign, $res);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}
	private function response($sign){
		$publickey = file_get_contents($this->rsaPublicKeyFilePath);  //获取用户公钥
		$ResponseTpl="<xml>  
							<alipay>
							<response>
							<success><![CDATA[%s]]></success>
							<biz_content><![CDATA[%s]]></biz_content>
							</response>
							<sign><![CDATA[%s]]></sign>
							<sign_type><![CDATA[%s]]></sign_type>
							</alipay>
                     </xml>"; 
		$resultStr = sprintf($ResponseTpl, $this->success,$publickey, $sign, $this->sign_type);  
        echo $resultStr;  
	}
}
?>
