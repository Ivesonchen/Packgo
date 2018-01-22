<?php
if(!isset($_GET['id'])&&!isset($_GET['res'])){
	?>
	<form action="tmp_class.php">
		<input type="text" name="id">
		<input type="submit">
	</form>
	<?php
	exit;
}

$cid=$_GET['id']+1-1;
$cclass=$_GET['class']+1-1;

require("data.php");
require("com_class.php");
if(isset($_GET['res'])){
	$id=$_POST['id']+1-1;
	$name=addslashes($_POST['name']);
	$price=$_POST['price']+1-1;
	$barcode=addslashes($_POST['barcode']);
	$cate=$_POST['cate']+1-1;
	$file=$_FILES['img'];
	$extname=end(explode(".",$file['name']));
	$newname='../file/com_goods/'.$id.'_'.date("Y-m-d");
	if(!is_dir($newname)){
		mkdir($newname,0777);
	}
	$newname=$newname.'/'.microtime(true).'.'.$extname;
	if(move_uploaded_file($file['tmp_name'],$newname)){
		$sql="update tradertb set name='$name',picture='$newname',classify=$cate,price=$price,barcode=$barcode where id=".$id;
		mysql_query($sql);
		header("Location:tmp_class.php?id=".$id."&class=".$cclass);
	}else{
		$sql="update tradertb set name='$name',classify=$cate,price=$price,barcode=$barcode where id=".$id;
		mysql_query($sql);
		header("Location:tmp_class.php?id=".$id."&class=".$cclass);
	}

	exit;
}
// $sql="select * from tradertb where id=".$cid;
$sql="select * from tradertb where id>".$cid." AND classify=".$cclass." LIMIT 1";
// echo $sql;
$res=mysql_query($sql);
$row=mysql_fetch_array($res);
?>
<meta charset="utf-8">
<style>
a {
	-webkit-tap-highlight-color:rgba(255,0,0,0);
	text-decoration:none;
}

input{
	margin:20px;
	padding:10px;
	font-size:16px;
	width:50vw;
}
select{
	margin:20px;
	padding:10px;
	font-size:16px;
	width:50vw;
}

.btn{
	float:left;
	display:inline-block;
	width:25vw;
	padding:10px 0px;
	color:#fff;
	margin:0;
	
}

.last{
	background:#4A90E2;
}
.next{
	background:#F5A623;
}

</style>

<div style="margin:20px auto;padding:20px 0px;width:70vw;">
	<?php
	if($row['picture']!=""){
		echo '已存在图像 : <img src="'.$row['picture'].'" width="200px"><br><br><hr>';
	}
	?>
	<center>
	<form action="tmp_class.php?class=<?php echo $cclass; ?>&res" method="post" enctype="multipart/form-data">
		
			<input type="hidden" name="id" value="<?php echo $row['id'];?>">
			<label>图片:</label><input type="file" name="img"><br/>
			产品名:<input type="text" name="name" value="<?php echo $row['name'];?>"><br/>
			条形码:<input type="text" name="barcode" value="<?php echo $row['barcode'];?>"><br/>
			价格 : <input type="text" name="price" value="<?php echo $row['price'];?>"><br/>
			分类 : <select name="cate"><?php foreach($classArray as $key=>$val){
				$key.='';
				if($row['classify']==$key){
					echo '<option value="'.$key.'" selected>'.$val.'</option>';
				}else{
					echo '<option value="'.$key.'">'.$val.'</option>';
				}
			}?></select><br/>
			<input type="submit" style="color:#fff;background-color:#f60;border:0px;cursor:pointer;" value="确认修改">
		
		
	</form>
	</center>
</div>
<center style='margin:0 0 0 24vw'>
	<a class='btn last' href="tmp_class.php?id=<?php echo $row['id'];?>&class=<?php echo $cclass; ?>">上一个</a>
	<a class='btn next' href="tmp_class.php?id=<?php echo $row['id'];?>&class=<?php echo $cclass; ?>">跳过</a>
</center>

<br><br>
