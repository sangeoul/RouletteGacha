
<?php

include "phplib.php";

dbset();
logincheck();
$errorv=false;

//echo ("GET ITEM ".$_POST['item_id']);


//아이템 정보를 읽어온다.
$qr="select * from items where id=".$_POST['item_id']." and active=1;";
$result=$dbcon->query($qr);
if($result && ($result->num_rows)>0){
	$itemdata=$result->fetch_array();
}
else{
	errorclose("잘못된 접근입니다. 아이템이 존재하지 않습니다");
	$errorv=true;
}


//가챠에서 얻은 아이템이 맞는지 점검한다.
$qr="select lastgacha from accounts where active=1 and match(lastgacha) against ('".$_POST['item_id']."' in boolean mode);";


$result=$dbcon->query($qr);
if($result->num_rows ==0){
	
	errorclose("획득 권한이 없는 아이템입니다.");
	$errorv=true;
}

if(!$errorv){

//이미 가지고 있는 아이템인지 점검해본다.
$qr="select * from collections where active=1 and userid=".$_SESSION['roulette_user_id']." and id=".$_POST['item_id'].";";

$result=$dbcon->query($qr);
//이미 가지고 있는 아이템이면 보유량을 1 증가시킨다.
if(($result->num_rows)==1){
	$dbcon->query("update collections set amount=(amount+1) where active=1 and id=".$_POST['item_id']." and userid=".$_SESSION['roulette_user_id'].";");
}
//아직 없는 아이템이면 insert into 로 추가해준다.
else if(($result->num_rows)==0){

	if(!$dbcon->query("insert into collections (name,id,category,amount,date,note,userid,username) values ('".$itemdata['name']."',".$_POST['item_id'].",".$itemdata['category'].",1,UTC_TIMESTAMP,'',".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."');")){

	errorclose("문제가 발생했습니다. DB Error Insert1");
	$errorv=true;
	}

}
	else{
		errorclose("문제가 발생했습니다. DB Error D1");
		$errorv=true;
	}

//lastgacha를 초기화시켜준다.
$dbcon->query("update accounts set lastgacha='0' where active=1 and id=".$_SESSION['roulette_user_id'].";");

if(!$errorv){
	errorclose("쉽을 획득했습니다.(".$itemdata['name'].")");

}
}

?>
