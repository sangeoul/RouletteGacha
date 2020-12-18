
<?php

	session_start();

	if(!isset($_SESSION['roulette_user_id'])){
		echo "<script language=javascript>window.location.href='./loginpage.php'</script>";
		
	}

	else{
		echo "<script language=javascript>window.location.href='./mainpage.php'</script>";
	}

?>