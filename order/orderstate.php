<?php
require('../data.php');
$orderid=addslashes($_POST['orderId']);
// $sql_sg=ceil($bid/100);
$sql_sg=1;
$temgoodsql="select * from orderhistorytb_".$sql_sg." where order_code='".$orderid."'";
$temgoodres=mysql_query($temgoodsql);
$temgoodnum=mysql_num_rows($temgoodres);
if($temgoodnum>0){
$row=mysql_fetch_array($temgoodres);

$re['error_code']=0;
$re['error_message']='';
$re['state']=$row['status'];
$re['orderNumber']=$orderid;
$re['goodsList']=[];        // 商品列表

$shopsql="select * from bussinesstb where id=".$row['bid'];
$shoprow=mysql_fetch_array(mysql_query($shopsql));

$re['shopIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($shoprow['head_img']);          // 商店头像
$re['shopName']=$shoprow['mallname'];         // 商店名称
$re['shopTel']='';


$re['userName']=$row['user_name'];      // 用户名
$re['userTel']=$row['phone'];           // 用户填写的联系电话
$re['userLocation']=$row['address'];    // 用户地址
$re['payMethod']=$row['type'];          // 支付方式
$re['orderTotalMoney']=$row['price'];   // 总价格
$re['orderState'] = 0;
$re['arriveTime'] = '';
$re['creatTime'] = '';

    $goodsarr=explode("#",$row['goodslist']);
    $goodsnum=count($goodsarr);
    for($j=0;$j<$goodsnum;$j++){
        $goodinfoar=explode("-",$goodsarr[$j]);
        $sql_sg=ceil($row['bid']/100);
        $gsql="select * from goodstb_$sql_sg where bid=".$row['bid']." and id=".$goodinfoar[0];
    	$gres=mysql_query($gsql);
	
    	$gnum=mysql_num_rows($gres);
	
    	if($gnum==0){
    	    $re['shopGoods'][$j]['id']=0;
	    	$re['shopGoods'][$j]['shopId']=$row['bid'];
	    	$re['shopGoods'][$j]['goodName']='商家已停售此商品';
	    	$re['shopGoods'][$j]['goodDesc']='';
	    	$re['shopGoods'][$j]['goodMonthSaleCount']=0;
	    	$re['shopGoods'][$j]['goodLikeCount']=0;
	    	$re['shopGoods'][$j]['sortId']=0;
	    	$re['shopGoods'][$j]['goodCount']=0;
	    	$re['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode('../file/sys/noimg.png');
	    	$re['shopGoods'][$j]['goodPrice']=0;
    	}else{
    	    $grow=mysql_fetch_array($gres);
    	    $re['shopGoods'][$j]['id']=$grow['id'];
	    	$re['shopGoods'][$j]['shopId']=$row['bid'];
	    	$re['shopGoods'][$j]['goodName']=$grow['name'];
	    	$re['shopGoods'][$j]['goodDesc']=$grow['remarks'];
	    	$re['shopGoods'][$j]['goodMonthSaleCount']=0;
	    	$re['shopGoods'][$j]['goodLikeCount']=0;
	    	$re['shopGoods'][$j]['sortId']=$grow['classify'];
	    	$re['shopGoods'][$j]['goodCount']=0;
	    	$re['shopGoods'][$j]['goodIcon']='http://t.hihigh.net/api/ddgImageDeal.php?url='.base64_encode($grow['picture']);
	    	$re['shopGoods'][$j]['goodPrice']=($grow['isdiscount']=='1')?$grow['discountprice']:$grow['price'];
    	    
    	}
	   
    }
    

}else{
$re['error_code']=1;
$re['error_message']='订单不存在';
}
echo json_encode($re);
?>