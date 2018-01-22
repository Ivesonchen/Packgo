<?php
require('../data.php');
$shopid=$_POST['shopId']+1-1;
		$gsql="select * from bussinesstb where id=".$shopid;
		$gres=mysql_query($gsql);
		$gnum=mysql_num_rows($gres);
		if($gnum>0){
		    $re['error_code']=0;
        	$re['error_message']='';
			$row=mysql_fetch_array($gres);
			$distance=round(sqrt(pow($drcw*$pybb*($row['longitude']-$locationX),2)+pow($dr*$pybb*($row['latitude']-$locationY),2))/1000,2);
			$sql_sg=ceil($row['id']/100);
    		$thistime=date("Y-m-d");
            $lastmonth=strtotime($thistime.' -1 month');
    		$yueshousql="select * from orderhistorytb_".$sql_sg." where status=2 and bid=".$row['id'].' and time>'.$lastmonth;
    		$yueshou=mysql_num_rows(mysql_query($yueshousql));
    		
			$re['shops']['id']=$row['id'];
			$re['shops']['shopName']=$row['mallname'];
			$re['shops']['deliverMoney']=$row['distribution_money'];
			$re['shops']['deliverTime']=$row['starttime'].':00-'.$row['endtime'].':00';
			$re['shops']['shop_location']=$row['address'];
			$re['shops']['peisongMoney']=$row['distribution_money'];
			$re['shops']['shopTel']=$row['phone'];
			$re['shops']['deliver_info']=$row['send_info'];
			$re['shops']['star']=$row['prestige'];
			$re['shops']['month_sale']=$yueshou;
			$re['shops']['desc']=$row['description'];
			$re['shops']['peisongMoney']=$row['distribution_money'];
			$re['shops']['iconUrl']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['head_img']);
			$re['shops']['distance']=$distance;
			$re['shops']['vote']='0';
			$cxsql="select * from goodstb_".ceil($row['id']/100).' where isdiscount=1 and bid='.$row['id'];
    		$csnum=mysql_num_rows(mysql_query($cxsql));
    		if($csnum>0){
    		    $re['shops']['flags'][]=array("id"=>"1","con"=>"惠","info"=>"商店含有优惠商品");
    		}
    		if($row["malltype"]=="LINK"){
    		    $re['shops']['flags'][]=array("id"=>"2","con"=>"连","info"=>"连锁商店");
    		}
    		if($row["isProved"]=="1"){
    		    $re['shops']['flags'][]=array("id"=>"3","con"=>"认","info"=>"袋袋购认证");
    		}
			$snum++;
		}else{
		    	$re['error_code']=1;
            	$re['error_message']='商店不存在';
		    
		}

echo json_encode($re);