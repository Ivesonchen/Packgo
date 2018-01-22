<?php
require('../data.php');
$uid=$_POST['uid']+1-1;
$bid=$_POST['shopId']+1-1;
$con='#|'.$bid.'#|';
$jcsql="select * from infotb where id=".$uid;
$jcres=mysql_query($jcsql);
$jcnum=mysql_num_rows($jcres);

if($jcnum>0){
	$jcrow=mysql_fetch_array($jcres);
	$shop_collect=$jcrow['shop_collect'];
	
	if(strpos($shop_collect,$con)===false){
		$newcon=$shop_collect.$con;
		$upsql="update infotb set shop_collect='$newcon' where id=".$uid;
		mysql_query($upsql);
	}
	
	$re['error_code']=0;
    $re['error_message']='OK';
}
else
{
   $re['error_code']=1;
	$re['error_message']='用户不存在';
}

echo json_encode($re);