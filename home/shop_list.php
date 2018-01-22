<?php

require('../data.php');



$locationX=$_GET['longitude']+1-1;//经
$locationY=$_GET['latitude']+1-1;//经纬度+1-1为了防止sql注入攻击


$pybb=0.017453; //  Pi/180
$dr=6370693.5; //  地球半径

$drcw=cos($locationY)*$dr;

$sql="select * from bussinesstb where POWER($drcw*$pybb*(longitude-$locationX),2)+POWER($dr*$pybb*(latitude-$locationY),2)<POWER(4000,2)";//从数据库中挑选出当时位置经纬度周围4000米的商家信息

$res=mysql_query($sql);
$num=mysql_num_rows($res);


if($num==0){
    /** 附近没有商家的时候shops返回空数组
     *  sliderImages返回权最高的轮播图
     */
	$re['error_code']=0;
	$re['error_message']='附近没有商家';
	$re['shops'] = [];
}else{
	$re['error_code']=0;
	$re['error_message']='OK';
	$snum=0;
	while($row=mysql_fetch_array($res)){
	
		$distance=round(sqrt(pow($drcw*$pybb*($row['longitude']-$locationX),2)+pow($dr*$pybb*($row['latitude']-$locationY),2))/1000,2);//距离
		
		$re['shops'][$snum]['id']=$row['id'];
		
		$sql_sg=ceil($row['id']/100);
		
		$thistime=date("Y-m-d");
		$lastmonth=strtotime($thistime.' -1 month');//计算出一个月之前的时间点
		
		$yueshousql="select * from orderhistorytb_".$sql_sg." where status=2 and bid=".$row['id'].' and time>'.$lastmonth;
		$yueshou=mysql_num_rows(mysql_query($yueshousql));
		
		$re['shops'][$snum]['shopName']=$row['mallname'];
		$re['shops'][$snum]['deliverMoney']=$row['price'];
		$re['shops'][$snum]['deliverTime']=$row['starttime'].':00-'.$row['endtime'].':00';
		$re['shops'][$snum]['shop_location']=$row['address'];
		$re['shops'][$snum]['deliver_info']=$row['send_info'];
		$re['shops'][$snum]['star']=$row['prestige'];
		$re['shops'][$snum]['peisongMoney']=$row['distribution_money'];
		$re['shops'][$snum]['shopTel']=$row['phone'];
		$re['shops'][$snum]['month_sale']=$yueshou;
		$re['shops'][$snum]['desc']=$row['description'];
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


	$re['sliderImages'][0]['id'] = 1;
	$re['sliderImages'][0]['desc'] = 'NONE';
	$re['sliderImages'][0]['url'] = 'http://t.hihigh.net/api/ddgImageDeal.php?fun=no&url='.base64_encode('../file/lunbo/1.jpg');
    
    $re['sliderImages'][1]['id'] = 2;
	$re['sliderImages'][1]['desc'] = 'NONE';
	$re['sliderImages'][1]['url'] = 'http://t.hihigh.net/api/ddgImageDeal.php?fun=no&url='.base64_encode('../file/lunbo/2.jpg');

    $re['sliderImages'][2]['id'] = 3;
	$re['sliderImages'][2]['desc'] = 'NONE';
	$re['sliderImages'][2]['url'] = 'http://t.hihigh.net/api/ddgImageDeal.php?fun=no&url='.base64_encode('../file/lunbo/3.jpg');


echo json_encode($re);