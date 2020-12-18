<html>
<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'>

<head><title>Cancel Roulette</title></head>

<body>

<?php

include 'phplib.php';
dbset();
logincheck();

if(!isset($_GET['rn'])){

	errorclose("잘못된 접근입니다.");

}
else{
	
	$qr= "select * from roulette where starter=".$_SESSION['roulette_user_id']." and number=".$_GET['rn']." and sold=1;";

	if(!$dbcon->query($qr))errorclose("잘못된 호출입니다. DB Error");

	$result=$dbcon->query($qr);

	if(($result->num_rows)!=1){
		errorclose("잘못된 호출입니다. DB Error");
	}
	
	else{
	
		$roulettedata=$result->fetch_array();

		//userdata구해오기
		$qr="select * from accounts where id=".$_SESSION['roulette_user_id']." AND active=1;";
		$result=$dbcon->query($qr);
		$userdata=$result->fetch_array();

		//트랜잭션에서 $trid 구해오기.
		$qr="select id from transactions where userid=".$_SESSION['roulette_user_id']." order by id desc";
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=$topid[0]+1;


		//룰렛 제거 쿼리
		$qr1="delete from roulette where starter=".$_SESSION['roulette_user_id']." and number=".$_GET['rn']." and sold=1;";
		
		//돈을 돌려주는 쿼리
		$qr2="update accounts set balance=(balance+".($roulettedata['ticket_price']*1000000).") where id=".$_SESSION['roulette_user_id']." and active=1;";
		//트랜잭션에 기입하는 쿼리.
		$qr3="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".($roulettedata['ticket_price']*1000000).",".($userdata['balance']+($roulettedata['ticket_price']*1000000)).",UTC_TIMESTAMP,'환불');";
		 
		
		//쿼리는 합쳐서 한번에 처리한다.
		
		if( $dbcon->query($qr1) && $dbcon->query($qr2) && $dbcon->query($qr3) ) {
			echo "<script>\n";
			echo "window.opener.location.replace('/roulettelist.php');\n";
			echo "</script>";

			errorclose("성공적으로 룰렛이 취소되었습니다.");
		}
		else
		{ errorclose("문제가 발생했습니다. DB Error");}



	}





}

?>

</html>