<?php

include 'phplib.php';
dbset();
logincheck();

$qr="select * from accounts where id=".$_SESSION['roulette_user_id']." AND active=1;";
$result=$dbcon->query($qr);
$userdata=$result->fetch_array();


$qr="select * from roulette where number=".$_GET['rn'];
$result=$dbcon->query($qr);

if($result->num_rows == 0){
	errorclose("게임이 존재하지 않습니다.");
}

$gamedata=$result->fetch_array();


if($userdata['balance']<($gamedata['ticket_price']*1000000)){
 errorclose("잔고가 부족합니다.");

}

//최종구매
else{

	//참가유저 추가
	$members_list=explode(",",$gamedata['members_list']);
	$doubled=false;
	
	for($i=0;$i<sizeof($members_list);$i++){
		if(intval($members_list[$i])==intval($_SESSION['roulette_user_id'])){
			$doubled=true;
		}
	}

	if($members_list[intval($_GET['tn'])]!='0'){
		errorclose("이미 구매된 티켓입니다.");
	}

	else if(!$gamedata['duplication_allowed'] && $doubled){
		errorclose("중복 참여를 할 수 없는 룰렛입니다.");
	}


	else{
		$members_list[intval($_GET['tn'])]=$_SESSION['roulette_user_id'];
		$templist=$members_list[0];
		for($i=1;$i<sizeof($members_list);$i++){
			$templist=$templist.",".$members_list[$i];
		}
		
		//트랜잭션에서 $trid 구해오기.
		$qr="select id from transactions where userid=".$_SESSION['roulette_user_id']." order by id desc";
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=($topid[0]+1);

		$qr1="update roulette set members_list='".$templist."', sold=sold+1 where number=".$_GET['rn'].";\n";

		//금액 차감
		$money=$userdata['balance']-($gamedata['ticket_price']*1000000);
	
		$qr2="update accounts set balance=".$money." where id=".$_SESSION['roulette_user_id']." and active=1;";

		$qr3="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".($gamedata['ticket_price']*1000000).",".$money.",UTC_TIMESTAMP,'구매');";
		 
		
		//쿼리는 합쳐서 한번에 처리한다.
		if($dbcon->query($qr1)&&$dbcon->query($qr2)&&$dbcon->query($qr3)){
			echo "<script>\n\n";
			echo "window.opener.location.reload();\n window.close();";
			echo "</script>";
		}

		else{
			echo "ERROR : ".$qr1."/".$qr2;
			//errorclose("티켓 구매 도중 문제가 발생하였습니다 - DB Error");
		}

	}
}
?>
