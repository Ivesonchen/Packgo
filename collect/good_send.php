<?php
require('../data.php');
$uid=$_POST['uid']+1-1;
$bid=$_POST['shopId']+1-1;
$style=$_POST['isCollect']+1-1;//1是收藏 
$gid=$_POST['goodId']+1-1;
$con='#|'.$bid.'-'.$gid.'#|';
$jcsql="select * from infotb where id=".$uid;
$jcres=mysql_query($jcsql);
$jcnum=mysql_num_rows($jcres);

if($jcnum>0){
    
	$jcrow=mysql_fetch_array($jcres);
	$goods_collect=$jcrow['goods_collect'];
	
	if(strpos($goods_collect,$con)===false){
		$newcon=$goods_collect.$con;
		$upsql="update infotb set goods_collect='$newcon' where id=".$uid;
		mysql_query($upsql);
	}
}

$re['error_code']=0;
$re['error_message']='OK';