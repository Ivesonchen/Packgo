<?php
require('../user/sms.php');
require('../data.php');
require('../wechat.php');

$bid=$_POST['shopId']+1-1;
$sql_sg=ceil($bid/100);
$uid=addslashes($_POST['uid']);
$arriveTime=addslashes($_POST['arriveTime']);
$remark=addslashes($_POST['remark']);
$name=addslashes($_POST['name']);
$address=addslashes($_POST['address']);
$phone=addslashes($_POST['phone']);
$openid='';
$sendGoodsTime=addslashes($_POST['sendGoodsTime']);
if(isset($_POST['openid'])){
	$openid=addslashes($_POST['openid']);
}
$type=$_POST['type']+1-1;// 1:货到付款 2:POS 3:支付宝 4:微信 5：上门自提
$channel=$_POST['channel']+1-1;//微信：
$goods=array();
$aerr=0;
$berr=0;
$cerr=0;
$totalfee=0;
$status=0;
if($type==4){
	$status=4;
}
$orderinfo='';
$goodlist=json_decode($_POST['goods_list']);
$flagCount=count($goodlist);
$ujsql="select * from infotb where phone=".$phone;
$ujres=mysql_query($ujsql);
$ujnum=mysql_num_rows($ujres);

if($ujnum==0){
    
    $re['error_code']=10;
	$re['error_message']='用户不存在';
	$insql="insert into infotb values(null,'','','$phone','','','',100,1,'".microtime(true)."','','','','','','','')";
	mysql_query($insql);
	smssend($phone);
	echo json_encode($re);
    exit;
}else{
    $ujrow=mysql_fetch_array($ujres);
    if($ujrow['id']!=$uid){
        $re['error_code']=10;
        $re['error_message']='用户存在,手机号和用户id不匹配';
        smssend($phone);
        echo json_encode($re);
        exit;
    }
}

if($channel!='1'){
    $imei=addslashes($_POST['IMEI']);
    if($imei!=$ujrow['IMEI']){
        $re['error_code']=10;
        $re['error_message']='用户手机号与IMEI不匹配';
        smssend($phone);
        echo json_encode($re);
        exit;
    }
    
}
for($i=0;$i<$flagCount;$i++){
	$tem=$goodlist[$i];
	$tem->goodId=$tem->goodId+1-1;
	
	$tem->goodNum=$tem->goodNum+1-1;
	$gsql="select * from goodstb_$sql_sg where bid=".$bid." and id=".$tem->goodId;
	$gres=mysql_query($gsql);
	
	$gnum=mysql_num_rows($gres);
	
	if($gnum==0){
		$aerr++;
		$re['shopGoods'][$aerr-1]['id']=$tem->goodId;
		$re['shopGoods'][$aerr-1]['goodName']='';
		$re['shopGoods'][$aerr-1]['errorType']='3';
		$re['shopGoods'][$aerr-1]['goodPriceNow']='-1';
	}else{
		$grow=mysql_fetch_array($gres);
		$goods[$i-1]['id']=$tem->goodId;
		$goods[$i-1]['count']=$tem->goodNum>0?$tem->goodNum:1;
		$content[]=$goods[$i-1]['id'].'-'.$goods[$i-1]['count'];
		if(($grow['isdiscount']=='1'&&$grow['discountprice']==$tem->goodPrice)||$grow['price']==$tem->goodPrice){
			$goods[$i-1]['price']=$tem->goodPrice;
			$totalfee+=($goods[$i-1]['price'])*($goods[$i-1]['count']);
			$orderinfo.=$grow['name'].' ('.$goods[$i-1]['count'].'件)';
		}else if($grow['stock']=='0'){
			$cerr++;
			$re['shopGoods'][$cerr-1]['id']=$tem->goodId;
			$re['shopGoods'][$cerr-1]['goodName']=$grow['name'];
			$re['shopGoods'][$cerr-1]['errorType']='2';
			$re['shopGoods'][$cerr-1]['goodPriceNow']='-1';
		}else{
			$berr++;
			$re['shopGoods'][$berr-1]['id']=$tem->goodId;
			$re['shopGoods'][$berr-1]['goodName']=$grow['name'];
			$re['shopGoods'][$berr-1]['errorType']='1';
			$re['shopGoods'][$berr-1]['goodPriceNow']=($grow['isdiscount']=='1')?$grow['discountprice']:$grow['price'];
		}
	}
}

if($aerr>0){
	$re['error_code']=-1;
	$re['error_message']='订单错误,aerr>0';
}else if($berr>0){
	$re['error_code']=-1;
	$re['error_message']='订单错误,berr>0';
}else if($cerr>0){
	$re['error_code']=-1;
	$re['error_message']='订单错误,cerr>0';
}else{
	$contentarr=implode("#",$content);
	$orderid=($uid+2037100235).($row['bid']+1003217302).ceil(microtime(true));
	$sql="insert into orderhistorytb_$sql_sg values(null,'$orderid','','','',0,$uid,'$openid',$bid,'".microtime(true)."',0,'$sendGoodsTime','$channel',$totalfee,'$name','$address','$phone',$status,0,'$remark',$type,'$contentarr')";
	
	
	$mre=mysql_query($sql);
	
	if($mre){
		if($type=='4'&&$openid!=''){
			$wechat=new WeChat( 'wxa4a704db5f3ba9c9' , '1f3773bd15e4f0c0678fd62b939d934a');
			$wechatget=$wechat->GetUnifiedOrder($openid,$orderinfo,$orderid,$totalfee * 100 );
			
			if( is_array($wechatget) )
			{
			   	$re['OnlinePay']['type']=$type;
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
		    	$re['error_code']=0;
	            $re['error_message']='';
	            $re['order_code']=$orderid;
		    
		}
	}else{
	    $re['error_code']=1;
		$re['error_message']='数据库错误,$mre='.$mre;
		$re['shopGoods'][0]['id']=0;
		$re['shopGoods'][0]['goodName']='';
		$re['shopGoods'][0]['errorType']='';
		$re['shopGoods'][0]['goodPriceNow']='';
	}
}
echo json_encode($re);
