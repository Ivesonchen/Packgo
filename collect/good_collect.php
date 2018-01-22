<?php
require('../data.php');
$uid=$_POST['uid']+1-1;
$page=$_POST['currentPage']+1-1;
$every=$_POST['pageSize']+1-1;
$sql="select * from infotb where id=".$uid;
$res=mysql_query($sql);
$num=mysql_num_rows($res);
if($num>0){
	$re['error_code']=0;
	$re['error_message']='';
	$rows=mysql_fetch_array($res);
	$goodcollect=$rows['goods_collect'];
	$goodcollect=str_replace("#|#|","#|",$goodcollect);
	$goodarr=explode("#|",$goodcollect);
	array_pop($goodarr);
	array_shift($goodarr);
	$collectnum=count($goodarr);
	$j=0;
	for($i=0;$i<$collectnum;$i++){
		$goodinfo=explode("-",$goodarr[$i]);
		$sql_sg=ceil($goodinfo[0]/100);
		$gsql='select * from goodstb_'.$sql_sg.' where bid='.$goodinfo[0].' and id='.$goodinfo[1];
		$gres=mysql_query($gsql);
		$gnum=mysql_num_rows($gres);
		if($gnum>0){
			$row=mysql_fetch_array($gres);
			$shoprow=mysql_fetch_array(mysql_query("select * from bussinesstb where id=".$goodinfo[0]));
			$re['shopGoods'][$j]['id']=$row['id'];
			$re['shopGoods'][$j]['shopId']=$goodinfo[0];
			$re['shopGoods'][$j]['shopName']=$shoprow['mallname'];
			$re['shopGoods'][$j]['goodName']=$row['name'];
			$re['shopGoods'][$j]['goodDesc']='';
			$re['shopGoods'][$j]['goodMonthSaleCount']=0;
			$re['shopGoods'][$j]['goodLikeCount']=0;
			$re['shopGoods'][$j]['sortId']=$row['classify'];
			$re['shopGoods'][$j]['goodCount']=0;
			$re['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['picture']).'&w=100';
			$re['shopGoods'][$j]['goodPrice']=$row['isdiscount']=='1'?$row['discountprice']:$row['price'];
			$j++;
		}
	}
}else{
	$re['error_code']=1;
	$re['error_message']='用户不存在';
}
echo json_encode($re);