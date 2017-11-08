<?php
namespace app\index\controller;
class Index{
 	public function index(){ 
      //获得参数 signature nonce token timestamp echostr
     	$nonce     = $_GET['nonce'];
     	$token     = 'wexin';
     	$timestamp = $_GET['timestamp'];
     	$echostr   = Request::instance()->get("echostr");;
        $signature = $_GET['signature'];
         //形成数组，然后按字典序排序
        $array = array();
        $array = array($nonce, $timestamp, $token);
     	sort($array);
         //拼接成字符串,sha1加密 ，然后与signature进行校验
        $str = sha1( implode( $array ) );
        if( $str  == $signature && $echostr ){
            //第一次接入weixin api接口的时候
            echo  $echostr;
            exit;
        }
    }
  	public function access_token(){
  		$AppID = "wx8f610e249e8bc7b3";
  		$AppSecret = "928cce5fa3ad79fd38cf9eeb28096aca";
     	$file = file_get_contents("../public/access_token.php",true);
     	$result = explode(" ",$file);
		if (time() > $result["12"]){
	        $data = array();
	        $data['access_token'] = $this->getNewToken($AppID,$AppSecret);
	        $data['expires_in']=time()+7000;
			$filename = "../public/access_token.php";
	        $fp = fopen($filename, "w");
	        fwrite($fp, print_r($data, true));
	        fclose($fp);
	        $file = file_get_contents("../public/access_token.php",true);
	    }else{	    
	    	return $result['6'];
	    }
	}

  	 
  	public function getNewToken($AppID,$AppSecret){
	    $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$AppID."&secret=".$AppSecret;
	    $access_token_Arr =  $this->http_curl($TOKEN_URL);
	 	$access_token_Arr = json_decode($access_token_Arr, true);
	    return $access_token_Arr['access_token'];
	}

  	public function menu(){
  		$ACC_TOKEN = $this->access_token();
  		$ACC_TOKEN = rtrim($ACC_TOKEN);
		$url = "http://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACC_TOKEN;
  		$jsonmenu = '{ 
			  "button":[ 
			  { 
			   "name":"天气预报", 
				   "sub_button":[ 
				   { 
				    "type":"click", 
				    "name":"北京天气", 
				    "key":"天气北京"
				   }, 
				   { 
				    "type":"click", 
				    "name":"上海天气", 
				    "key":"天气上海"
				   }, 
				   { 
				    "type":"click", 
				    "name":"广州天气", 
				    "key":"天气广州"
				   }, 
				   { 
				    "type":"click", 
				    "name":"深圳天气", 
				    "key":"天气深圳"
				   }, 
				   { 
				    "type":"view", 
				    "name":"本地天气", 
				    "url":"http://m.hao123.com/a/tianqi"
				   }] 
			   
			  
				  }, 
				  { 
				   "name":"瑞雪", 
				   "sub_button":[ 
				   { 
				    "type":"click", 
				    "name":"公司简介", 
				    "key":"company"
				   }, 
				   { 
				    "type":"click", 
				    "name":"趣味游戏", 
				    "key":"游戏"
				   }, 
				   { 
				    "type":"click", 
				    "name":"讲个笑话", 
				    "key":"笑话"
				   }] 
			  }] 
		}'; 
		//dump(json_decode($jsonmenu));
	 	$postJson = urldecode($jsonmenu);
	 	$res = $this->http_curl($url,'post','json',$postJson);
	   	var_dump($res);

  	}

  	 public function responseMsg(){
		 //get post data, May be due to the different environments
  	 	//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
	  	 $postArr = file_get_contents("php://input");//接收微信发来的XML数据
	  	// $data = (array)simplexml_load_string($postArr);
	  	// dump($postArr);exit;
	  	//extract post data
	 	if(!empty($postStr)){
	   //解析post来的XML为一个对象$postObj
		   	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	 		// var_dump(2222);exit;
		   	$fromUsername = $postObj->FromUserName; //请求消息的用户
		   	$toUsername = $postObj->ToUserName; //"我"的公众号id
		   	$keyword = trim($postObj->Content); //消息内容
		   	$time = time(); //时间戳
		   	$msgtype = 'text'; //消息类型：文本
		   	$textTpl = "<xml>
				  <ToUserName><![CDATA[%s]]></ToUserName>
				  <FromUserName><![CDATA[%s]]></FromUserName>
				  <CreateTime>%s</CreateTime>
				  <MsgType><![CDATA[%s]]></MsgType>
				  <Content><![CDATA[%s]]></Content>
				  </xml>";	

		  	if($postObj->MsgType == 'event'){ //如果XML信息里消息类型为event
			 	if($postObj->Event == 'subscribe'){ //如果是订阅事件
				  	$contentStr = "欢迎订阅misaka去年夏天！\n更多精彩内容：http://blog.csdn.net/misakaqunianxiatian";
				  	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
				  	echo $resultStr;
				  	exit();
			 	}
			}
		  	switch ($keyword){
		  		case 1:
		  			$contentStr = '这是1';
  				break;
  				case 2:
			  		$contentStr = '这是2';
			  	break;
				case 3:
			  		$contentStr = '这是3';
			  	break;
			  	default:
  					$contentStr = '请输入1-3的数字';
				break;
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
			    echo $resultStr;
			    exit();
		  	}
		}else {
		   echo "未收到消息";
		   exit;
	  	}
 	}

 //  	function https_request($url,$data = null){
	//   	$curl = curl_init(); 
	//  	curl_setopt($curl, CURLOPT_URL, $url); 
	//  	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
	//  	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
	//  	if (!empty($data)){
	//         curl_setopt($curl, CURLOPT_POST, 1);
	//         curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
 //    	}
	//  	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	//  	$output = curl_exec($curl);
	//  	curl_close($curl); 
	//  	return $output; 
	// }

	public function http_curl($url,$type='get',$res='json',$arr=''){
        $ch  =curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($type == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
        }
        $output =curl_exec($ch);
        //curl_close($ch);
        if($res == 'json'){
        	if(curl_errno($ch)){
    			return curl_error($ch);
        	}else{
            	return json_decode($output,true);
        	}
        }
    }
}
