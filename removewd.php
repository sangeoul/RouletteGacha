<?php

include 'phplib.php';
dbset();
admincheck();


if(admincheck()){

	

	$qr1="update accounts set withdrawing=0 where active=1 and id=".$_GET['id'].";";
	echo ("DEBUG:".$qr1);

	
	if($dbcon->query($qr1)){
		echo("<script>\n alert('완료');\n history.back();\n </script>");
	}
	else {
		echo("<script>\n alert('실패');\n history.back();\n </script>");
	}
}


?>