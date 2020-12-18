<?php


	include 'phplib.php';
	dbset();
	logincheck();

	

	$qr="select * from attendance where id=".$_SESSION['roulette_user_id']." and date(date_add(attendance_date, interval -11 hour))=date(date_add(UTC_TIMESTAMP, interval -11 hour));";
	
	$result=$dbcon->query($qr);

	$attendance=$result->fetch_array();

	if($result->num_rows == 0){
		
		//출석 체크를 한다
		


		//체크
		$qr1="insert into attendance (id,attendance_date) values (".$_SESSION['roulette_user_id'].",UTC_TIMESTAMP);";
		

		//출석보상 ISK
		$qr2="update accounts set balance=(balance+".$ATTENDANCE_REWARD.") where active=1 and id=".$_SESSION['roulette_user_id'].";";


		$qr="select id from transactions where userid=".$_SESSION['roulette_user_id']." order by id desc;";
		if(!($dbcon->query($qr))){

			errorclose("에러 발생 . Calling DB Error 1");
		}
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=$topid[0]+1;

		$qr= "select balance from accounts where id=".$_SESSION['roulette_user_id']." and active=1;";
		if(!($dbcon->query($qr))){

			errorclose("에러 발생 . Calling DB Error 2");
		}
		$result=$dbcon->query($qr);
		$balance=$result->fetch_array();
		$balance=$balance[0];

		$qr3="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".$ATTENDANCE_REWARD.", ".($balance+$ATTENDANCE_REWARD).",UTC_TIMESTAMP,'출석');";

		$qr4="update accounts set today_charged=0 where active=1 and id=".$_SESSION['roulette_user_id'].";";

		if(!$dbcon->query($qr1)){

			errorclose("출석체크에 문제가 발생했습니다. DB Error 1");		
		}
		else if(!$dbcon->query($qr2)) {

			errorclose("출석체크에 문제가 발생했습니다. DB Error 2");	

		}
		else if(!$dbcon->query($qr3)) {
			
			errorclose("출석체크에 문제가 발생했습니다. DB Error 3");	

		}
		else if(!$dbcon->query($qr4)) {
			
			errorclose("출석체크에 문제가 발생했습니다. DB Error 4");	

		}
		else{
			echo ("<script>\n window.opener.location.reload()\n </script>");
			errorclose("출석체크가 완료되었습니다.충전한도가 갱신되었습니다. \\n출석보상: ".number_format($ATTENDANCE_REWARD)." K");

		}
	}
	
	else{

		errorclose("오늘은 이미 출석했습니다.");

	}


?>