<?php
require('../data.php');
require('../wechat.php');
$orderid=addslashes($_POST['orderid']);
$jcsql="select * from orderhistorytb_1 where order_code='$orderid'";
$jcres=mysql_query($jcsql);
$jcnum=mysql_num_rows($jcres);
$totalfee=0;
$orderinfo='';
if($jcnum>0){
    $row=mysql_fetch_array($jcres);
    $bid=$row['bid'];
    $sql_sg=ceil($bid/100);
    $goodslistarr=explode("#",$row['goodslist']);
    $goodnum=count($goodslistarr);
    for($i=0;$i<$goodnum;$i++){
            $temarr=explode("-",$goodslistarr[$i]);
            $gid=$temarr[0];
            $gnum=$temarr[1];
            $gsql="select * from goodstb_$sql_sg where bid=".$bid." and id=".$gid;
	        $gres=mysql_query($gsql);
	        $gnum=mysql_num_rows($gres);
	        if($gnum>0){
	            $grow=mysql_fetch_array($gres);
	            $orderinfo.=$grow['name'].' ('.$gnum.'件)';
	        }
        
    }
    $totalfee=$row['price'];
            $wechat=new WeChat( 'wxa4a704db5f3ba9c9' , '1f3773bd15e4f0c0678fd62b939d934a');
			$wechatget=$wechat->GetUnifiedOrder( $row['openid'],$orderinfo,$orderid,$totalfee * 100 );
			
			if( is_array($wechatget) )
			{
			   	$re['OnlinePay']['type']='4';
			    $re['OnlinePay']['timestamp']=$wechatget['timeStamp'];
			    $re['OnlinePay']['nonceStr']=$wechatget['nonceStr'];
		    	$re['OnlinePay']['package']=$wechatget['package'];
		    	$re['OnlinePay']['signType']=$wechatget['signType'];
		    	$re['OnlinePay']['paySign']=$wechatget['paySign'];
                $re['OnlinePay']['xml']=$wechatget['xml'];
		    	$re['error_code']=0;
	            $re['error_message']='';
	            $re['order_code']=$orderid;
	            
			}
		    else
		    {
		        $re['error_code'] = 6;
		        $re['error_message'] = $wechatget;
		    }
}else{
    $re['error_code'] = 1;
	$re['error_message'] = '订单不存在';
}
echo json_encode($re);




