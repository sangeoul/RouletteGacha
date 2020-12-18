<?php
include 'phplib.php';

dbset();
logincheck();


$qr="select * from accounts where active=1 and id=".$_SESSION['roulette_user_id'];
$result=$dbcon->query($qr);
$userdata=$result->fetch_array();
$balance=$userdata['balance'];

$amount=intval($_POST['amount']);

if($amount>$balance){

	errorclose("잔고를 넘는 금액을 신청했습니다.");
}

else if ($amount<($MIN_WITHDRAW*1000000)){
errorclose($MIN_WITHDRAW." 밀이 넘는 금액만 신청할 수 있습니다. 그보다 적은 금액을 신청할 경우 따로 문의주세요.");
}

else{
	$qr="update accounts set withdrawing=(withdrawing+".$amount."), balance=(balance-".$amount.")  where active=1 and id=".$_SESSION['roulette_user_id'].";";


	$mqr="select id from transactions order by id desc limit 1";
	$result=$dbcon->query($mqr);
	$maxid=$result->fetch_row();
echo("DEBUG:");
	$qr2="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".($maxid[0]+1).",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".$amount.",".($balance-$amount).", UTC_TIMESTAMP, '출금');";


	if(!($dbcon->query($qr))){
		echo ("<script>window.opener.location.reload();</script>");
		errorclose("문제가 발생했습니다. DB Error 1");

	}

	else if(!($dbcon->query($qr2))){
		echo ("<script>window.opener.location.reload();</script>");
		echo("문제가 발생했습니다. DB Error 2 : ".$qr2);

	}
	

	else{
		echo ("<script>window.opener.location.reload();</script>");
		errorclose("완료되었습니다.");
		
	}
}

?>