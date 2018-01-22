<?php
require('../data.php');
$uid=$_POST['uid']+1-1;
$sql="select * from infotb where id=".$uid;
$res=mysql_query($sql);
$num=mysql_num_rows($res);
if($num>0){
	$re['error_code']=0;
	$re['error_message']='';
	$rows=mysql_fetch_array($res);
	$shopcollect=$rows['shop_collect'];
	$shopcollect=str_replace("#|#|","#|",$shopcollect);
	$shoparr=explode("#|",$shopcollect);
	array_pop($shoparr);
	array_shift($shoparr);
	$collectnum=count($shoparr);
	$j=0;
	$snum=0;
	$re['shops']=array();
	for($i=$collectnum-1;$i>=0;$i--){
		$gsql="select * from bussinesstb where id=".$shoparr[$i];
		$gres=mysql_query($gsql);
		$gnum=mysql_num_rows($gres);
		if($gnum>0){
			$row=mysql_fetch_array($gres);
			$distance=round(sqrt(pow($drcw*$pybb*($row['longitude']-$locationX),2)+pow($dr*$pybb*($row['latitude']-$locationY),2))/1000,2);
			$sql_sg=ceil($row['id']/100);
    		$thistime=date("Y-m-d");
            $lastmonth=strtotime($thistime.' -1 month');
    		$yueshousql="select * from orderhistorytb_".$sql_sg." where status=2 and bid=".$row['id'].' and time>'.$lastmonth;
    		$yueshou=mysql_num_rows(mysql_query($yueshousql));
    		
			$re['shops'][$snum]['id']=$row['id'];
			$re['shops'][$snum]['shopName']=$row['mallname'];
			$re['shops'][$snum]['deliverMoney']=$row['price'];
			$re['shops'][$snum]['deliverTime']=$row['starttime'].':00-'.$row['endtime'].':00';
			$re['shops'][$snum]['shop_location']=$row['address'];
			$re['shops'][$snum]['peisongMoney']=$row['distribution_money'];
			$re['shops'][$snum]['shopTel']=$row['phone'];
			$re['shops'][$snum]['deliver_info']=$row['send_info'];
			$re['shops'][$snum]['star']=$row['prestige'];
			$re['shops'][$snum]['month_sale']=$yueshou;
			$re['shops'][$snum]['desc']=$row['description'];
			$re['shops'][$snum]['peisongMoney']=$row['distribution_money'];
			$re['shops'][$snum]['iconUrl']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['head_img']);
			$re['shops'][$snum]['distance']=$distance;
			$re['shops'][$snum]['vote']='0';
			$re['shops'][$snum]['flags']=[];
			
			$cxsql="select * from goodstb_".ceil($row['id']/100).' where isdiscount=1 and bid='.$row['id'];
    		$csnum=mysql_num_rows(mysql_query($cxsql));
    		if($csnum>0){
    		    $re['shops'][$snum]['flags'][]=array("id"=>"1","con"=>"惠","info"=>"商店含有优惠商品");
    		}
    		if($row["malltype"]=="LINK"){
    		    $re['shops'][$snum]['flags'][]=array("id"=>"2","con"=>"连","info"=>"连锁商店");
    		}
    		if($row["isProved"]=="1"){
    		    $re['shops'][$snum]['flags'][]=array("id"=>"3","con"=>"认","info"=>"袋袋购认证");
    		}
			$snum++;
		}
	}
}else{
	$re['error_code']=1;
	$re['error_message']='用户不存在';
}
echo json_encode($re);