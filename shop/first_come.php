<?php
require('../data.php');
$num=$_POST['pageSize']+1-1;

require('../com_class.php');
$shopid=$_POST['shopId']+1-1;

$page=$_POST['currentPage']+1-1;

if($page<0)$page=0;
$start=$page*$num;
$sql_sg=ceil($shopid/100);
$classNum=count($classArray);


$re['error_code']=0;
$re['error_message']='';
for($i=0;$i<$classNum;$i++){
	$sql='select * from goodstb_'.$sql_sg.' where bid='.$shopid.' and stock=1 and classify='.$i.' order by id limit '.$start.','.$num;
	$res=mysql_query($sql);
	$gnum=mysql_num_rows($res);
	
	
	$j=0;
	$re['firstSorts'][$i]['id']=$i;
	$re['firstSorts'][$i]['shopId']=$shopid;
	$re['firstSorts'][$i]['sortName']=$classArray[$i];
	$re['firstSorts'][$i]['num']=$gnum;
	while($row=mysql_fetch_array($res)){
		$re['firstSorts'][$i]['shopGoods'][$j]['id']=$row['id'];
		$re['firstSorts'][$i]['shopGoods'][$j]['shopId']=$shopid;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodName']=$row['name'];
		$re['firstSorts'][$i]['shopGoods'][$j]['goodDesc']='';
		$re['firstSorts'][$i]['shopGoods'][$j]['goodMonthSaleCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodLikeCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['sortId']=$i;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodCount']=0;
		$re['firstSorts'][$i]['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($row['picture']);
		$re['firstSorts'][$i]['shopGoods'][$j]['goodPrice']=$row['isdiscount']=='1'?$row['discountprice']:$row['price'];
		if($row['isdiscount']=='1'){
		    	$re['firstSorts'][$i]['shopGoods'][$j]['oldPrice']=$row['price'];
		}
		$j++;
	}
}
echo json_encode($re);



