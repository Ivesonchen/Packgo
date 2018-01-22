<?php
require('../data.php');
$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
$fp=fopen("id.txt","w");
fwrite($fp,$xml);
fclose($fp);
$xml=str_replace("\r\n","",$xml);
preg_match('/<out_trade_no><\!\[CDATA\[([0-9a-zA-Z]*)\]\]>/i',$xml,$match);
$orderid=$match[1];
$jcsql="select * from orderhistorytb_1 where status=4 and order_code='$orderid'";
$jcnum=mysql_num_rows(mysql_query($jcsql));
if($jcnum>0){
    if(strpos($xml,"<result_code><![CDATA[SUCCESS]]>")){
        preg_match('/<transaction_id><\!\[CDATA\[([0-9a-zA-Z]*)\]\]>/i',$xml,$omatch);
    	$wechatordercode=$omatch[1];
    	$sql="update orderhistorytb_1 set status=0,is_fee=1,wechat_order_code='$wechatordercode' where order_code='$orderid'";
    
    }else{
    	$sql="update orderhistorytb_1 set status=4 where order_code='$orderid'";
    }
    mysql_query($sql);
}


?>
