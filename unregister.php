<?php

include 'phplib.php';
dbset();
logincheck();


if(!isset($_POST['inputnamehere'],$_SESSION['roulette_user_name'])){

	echo "<script language=javascript>alert('잘못된 접근입니다.');close();</script>";


}

//제대로 적었으면 계정 비활성화
else if( $_SESSION['roulette_user_name']===$_POST['inputnamehere']){

	$qr1="update roulette set starter=1 where starter=".$_SESSION['roulette_user_id']." and iscompleted=0;";
		
//트랜잭션 액티브 제로
	$qr2="update transactions set active=0 where id=".$_SESSION['roulette_user_id'].";";

//컬렉션 액티브 제로
	$qr3="update collections set active=0 where userid=".$_SESSION['roulette_user_id'].";";

//어카운트 액티브 제로
	$qr4="update accounts set active=0 , deleted_date=UTC_TIMESTAMP where name='".$_SESSION['roulette_user_name']."' AND active=1";
	if($dbcon->query($qr1) && $dbcon->query($qr2) && $dbcon->query($qr3) && $dbcon->query($qr4)) {

		echo "<script> alert('계정이 삭제되었습니다.');";
		echo "window.opener.location.href='./logout.php';";
		echo "close();</script>";

	}
	else{
		echo "<script> alert('계정 삭제 중 DB 문제가 발생했습니다. 관리자에게 문의해주세요.');";
		echo "close();</script>";
	}
}

else{
		echo "<script> alert('캐릭터 이름을 잘못 입력했습니다.');";
		echo "close();</script>";

}

?>