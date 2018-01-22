<?php
require('../data.php');
$id=$_POST['uid']+1-1;
$jcsql="select * from infotb where id=".$id;
$jcnum=mysql_num_rows(mysql_query($jcsql));
if($jcnum>0){
    $con=addslashes($_POST['remarks']);
    $sql="insert into feedback values($id,2,'$con',0,'')";
    mysql_query($sql);
    $re['error_code']=0;
    $re['error_message']='';
}else{
    $re['error_code']=1;
    $re['error_message']='用户不存在';
}
echo json_encode($re);