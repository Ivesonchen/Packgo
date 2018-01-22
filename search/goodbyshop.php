<?php
require('../data.php');
$con=addslashes($_POST['content']);
$page=$_POST['currentPage']+1-1;
$every=$_POST['pageSize']+1-1;
$bid=$_POST['shopId']+1-1;
$sql_sg=ceil($bid/100);
$sql="select * from goodstb_$sql_sg where bid=".$bid." and stock=1 and name like '%$con%'";
$res=mysql_query($sql);
$num=mysql_num_rows($res);
if($num==0){
	$re['error_code']=1;
	$re['error_message']='没有此商品';
}else{
	$re['error_code']=0;
	$re['error_message']='';
	
	$tolpage=ceil($num/$every);

	$start=$page*$every;
	$sql="select * from goodstb_$sql_sg where bid=".$bid." and stock=1 and name like '%$con%' order by id desc limit $start,$every";
	$res=mysql_query($sql);
	
	$snum=0;
	while($row=mysql_fetch_array($res)){
		$re['shopGoods'][$snum]['id']=$row['id'];
		$re['shopGoods'][$snum]['goodName']=$row['name'];
		$re['shopGoods'][$snum]['goodDesc']='';
		$re['shopGoods'][$snum]['goodMonthSale']=0;
		$re['shopGoods'][$snum]['goodLikeCount']=0;
		$re['shopGoods'][$snum]['sortId']=$row['classify'];
		$re['shopGoods'][$snum]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['picture']);
		$re['shopGoods'][$snum]['goodPrice']=$row['isdiscount']=='1'?$row['discountprice']:$row['price'];
        if($row['isdiscount']=='1'){
		    	$re['shopGoods'][$snum]['oldPrice']=$row['price'];
		}
		$snum++;
	}
}
echo json_encode($re);
