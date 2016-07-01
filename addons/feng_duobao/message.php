<?php
class WX_message{
	 public function WX_request($url,$data=null){
       $curl = curl_init(); // 启动一个CURL会话
       curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
       curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
       if($data != null){
           curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
       }
       curl_setopt($curl, CURLOPT_TIMEOUT, 300); // 设置超时限制防止死循环
       curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
       $info = curl_exec($curl); // 执行操作
       if (curl_errno($curl)) {
           echo 'Errno:'.curl_getinfo($curl);//捕抓异常
           dump(curl_getinfo($curl));
       }
       return $info;
   }
}

?>