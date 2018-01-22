<?php
require('../data.php');
require('../com_class.php');
$shopid=$_POST['shopId']+1-1;
$size=$_POST['pageSize']+1-1;
$page=$_POST['currentPage']+1-1;
$sort=$_POST['sortId']+1-1;
$sql_sg=ceil($shopid/100);
$sql='select * from goodstb_'.$sql_sg.' where bid='.$shopid.' and stock=1 and classify='.$sort.' limit '.(($page)*$size).','.$size;
$res=mysql_query($sql);
if($res){
	$gnum=mysql_num_rows($res);
	$j=0;
	$i=0;
	$re['error_code']=0;
	$re['error_message']='Server OK';
	$re['firstSorts'][$i]['id']=$sort;
	$re['firstSorts'][$i]['shopId']=$shopid;
	$re['firstSorts'][$i]['sortName']=$classArray[$sort];
	$re['firstSorts'][$i]['num']=$gnum;
	while($row=mysql_fetch_array($res)){
		$re['firstSorts'][$i]['shopGoods'][$j]['id']=$row['id'];
		$re['firstSorts'][$i]['shopGoods'][$j]['shopId']=$shopid;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodName']=$row['name'];
		$re['firstSorts'][$i]['shopGoods'][$j]['goodDesc']='';
		$re['firstSorts'][$i]['shopGoods'][$j]['goodMonthSaleCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodLikeCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['sortId']=$sort;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['picture']);
		$re['firstSorts'][$i]['shopGoods'][$j]['goodPrice']=$row['isdiscount']=='1'?$row['discountprice']:$row['price'];
		if($row['isdiscount']=='1'){
		    	$re['firstSorts'][$i]['shopGoods'][$j]['oldPrice']=$row['price'];
		}
		$j++;
	}
}else{
	$re['error_code']=1;
	$re['error_message']='Server Error';
}

echo json_encode($re);