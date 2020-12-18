<html>
<head>
<title>Capsuleer's Roulette</title>
<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
</head>


<?php

include 'phplib.php';

dbset();
logincheck();



$duplication_allowed;
$error=false;


//룰렛을 만드는 것인지 점검 - 이는 혹시 나중에 다른 형태의 게임을 추가할 때 호환성 및 구분을 위해서이다.
if($_POST['gametype']=='roulette'){


	//duplication 변수는 checkbox 로 넘겨줬기 때문에 특별한 처리가 필요.
	if(!isset($_POST['duplication'])){
		$duplication_allowed=1;
	}
	else if(intval($_POST['duplication'])==1) $duplication_allowed=0;

	else{
		errorback("문제 발생. 중복금지 체크박스 에러.");
		$error=true;
	}



	if(!is_numeric($_POST['price'])){
		errorback("티켓 가격이 제대로 입력되지 않았습니다.");
		$error=true;
	}
	else if(!is_numeric($_POST['membersnumber'])){
		errorback("참가자 숫자가 제대로 입력되지 않았습니다.");
		$error=true;
	}
	else if(!is_numeric($_POST['mynumber'])){
		errorback("참가할 티켓 번호가 제대로 입력되지 않았습니다.");
		$error=true;
	}

	else if($_POST['userid']!=$_SESSION['roulette_user_id']){
		errorback("로그인 정보가 맞지 않습니다.");
		$error=true;
	}

	$members_list_string;	
	
	//나는 정수형이 조아~~
	$price=intval($_POST['price']);
	$membersnumber=intval($_POST['membersnumber']);
	$mynumber=intval($_POST['mynumber']);
	$prize=floor($price*$membersnumber*$FEE_APPLIED);

	if($mynumber==1){
		$members_list_string="".$_SESSION['roulette_user_id'];
	}
	else{
		$members_list_string="0";
	}


	for( $i=1; $i<$membersnumber;$i++){
		
		if(($mynumber-1)==$i){
			$members_list_string=$members_list_string.",".$_SESSION['roulette_user_id'];
		}
		else{
			$members_list_string=$members_list_string.",0";
		}

	}

	$qr="select balance from accounts where active=1 and id=".$_SESSION['roulette_user_id'];
	
	$result=$dbcon->query($qr);
	
	$balance=$result->fetch_array();

	$qrp="select * from roulette where starter=".$_SESSION['roulette_user_id']." and iscompleted=0;";
	$resultp=$dbcon->query($qrp);
	$qra="select * from roulette where iscompleted=0;";
	$resulta=$dbcon->query($qra);

	if($error){
		echo ("<script language=javascript>window.history.back();</script>");
	}
	else if(($resultp->num_rows)>=$MAX_ACTIVE_ROULETTES_PER_USER){

		errorclose("동시에 만들 수 있는 룰렛의 한계를 초과했습니다.한계: 1인당".$MAX_ACTIVE_ROULETTES_PER_USER."개.");

	}


	else if(($resulta->num_rows)>=$MAX_ACTIVE_ROULETTES){

		errorclose("동시에 만들 수 있는 룰렛의 한계를 초과했습니다.한계: ".$MAX_ACTIVE_ROULETTES."개.");

	}

	//거지는 시작도 못하는 도박.
	else if($balance[0] < $price*1000000){
		errorback("잔고가 부족합니다.");
		
	}

	else {
		
		$qr="update accounts set balance=(balance-".($price*1000000).") where active=1 and id=".$_SESSION['roulette_user_id'].";";

		if(!($dbcon->query($qr))){

			errorclose("에러 발생 . DB Error 1");
		}
		$qr="select id from transactions where userid=".$_SESSION['roulette_user_id']." order by id desc";
		if(!($dbcon->query($qr))){

			errorclose("에러 발생 . DB Error 2");
		}
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=$topid[0]+1;

		$qr1="insert into roulette (starter, ticket_price, prize, starttime, members_number, members_list, duplication_allowed) values (".$_SESSION['roulette_user_id']." , ".$price." , '".$prize." mil K' , UTC_TIMESTAMP, ".$membersnumber." , '".$members_list_string."', ".$duplication_allowed.");";

		$qr2="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".($price*1000000).",".($balance[0]-($price*1000000)).",UTC_TIMESTAMP,'구매');";



		if(!($dbcon->query($qr1) && $dbcon->query($qr2))) {

			

			errorclose("에러 발생 . DB Error 3");
		}
		echo ("<script>window.opener.location.reload();</script>");
		errorclose("등록되었습니다.");
	}
	
}



?>

</html>