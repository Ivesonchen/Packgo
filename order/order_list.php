<?php
require('../data.php');
$uid=$_POST['uid']+1-1;
$page=$_POST['currentPage']+1-1;
$every=$_POST['pageSize']+1-1;
if($page<0)$page=0;
$start=($page)*$every;
$sql='select * from orderhistorytb_1 where uid='.$uid.' order by id desc limit '.$start.','.$every;
$res=mysql_query($sql);
$num=mysql_num_rows($res);
if($num<=0){
    /** 没有订单的时候订单列表orders为空数组 */
	$re['error_code']=0;
	$re['error_message']='不存在订单';
	$re['orders']=[];
}else{
	
	$re['error_code']=0;
	$re['error_message']='';
	

	$i=0;
	while($row=mysql_fetch_array($res)){
		$re['orders'][$i]['userLocation']=$row['address'];
		$re['orders'][$i]['userName']=$row['user_name'];
		$re['orders'][$i]['userTel']=$row['phone'];
		$re['orders'][$i]['orderNumber']=$row['order_code'];
		$re['orders'][$i]['orderState']=$row['status'];
		$re['orders'][$i]['payMethod']=$row['type'];
		$re['orders'][$i]['arriveTime']='';
		$re['orders'][$i]['desc']=$row['remark'];
		
		$usql="select * from bussinesstb where id=".$row['bid'];
		$ures=mysql_query($usql);
		$urow=mysql_fetch_array($ures);
		
		$re['orders'][$i]['shopName']=$urow['mallname'];
		$re['orders'][$i]['shopTel']=$urow['phone'];
		$re['orders'][$i]['shopIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($urow['head_img']);
		
		$re['orders'][$i]['orderCreateTime']=date("Y-m-d",$row['time']);
		$temgood=explode("#",$row['goodslist']);
		$tolprice=0;
		$j=0;
		foreach($temgood as $key=>$val){
			$temsval=explode("-",$val);
			$goodid=$temsval[0];
			$goodnum=$temsval[1];
			$sql_sg=ceil($row['bid']/100);
			$temgoodsql="select * from goodstb_".$sql_sg." where id=".$goodid.' and bid='.$row['bid'];
			$temgoodres=mysql_query($temgoodsql);
			$temgoodnum=mysql_num_rows($temgoodres);
			if($temgoodnum>0&&$goodnum>0){
				$temgoodrow=mysql_fetch_array($temgoodres);
				if($temgoodrow['isdiscount']=='1'){
					$temprice=$temgoodrow['discountprice'];
					$temgoodrow['name']='【折】'.$temgoodrow['name'];
				}else{
					$temprice=$temgoodrow['price'];
				}
				$re['orders'][$i]['shopGoods'][$j]['id']=$goodid;
				$re['orders'][$i]['shopGoods'][$j]['shopId']=$row['bid'];
				$re['orders'][$i]['shopGoods'][$j]['goodName']=$temgoodrow['name'];
				$re['orders'][$i]['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($temgoodrow['picture']);
				$re['orders'][$i]['shopGoods'][$j]['goodDesc']=$temgoodrow['remarks'];
				$re['orders'][$i]['shopGoods'][$j]['goodMonthSaleCount']=0;
				$re['orders'][$i]['shopGoods'][$j]['goodLikeCount']=0;
				$re['orders'][$i]['shopGoods'][$j]['goodPrice']=$temprice;
				$re['orders'][$i]['shopGoods'][$j]['goodCount']=$goodnum;
				$re['orders'][$i]['shopGoods'][$j]['sortId']=$temgoodrow['classify'];
				$tolprice+=($temprice*$goodnum);
				
			}
			$j++;
			
		}
		$re['orders'][$i]['orderTotalMoney']=$tolprice;
		$i++;
	}
}
echo json_encode($re);