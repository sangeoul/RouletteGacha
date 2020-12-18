<?php

include 'phplib.php';
dbset();
admincheck();
menutable();

if(admincheck()){


	$qr="select * from accounts where active=1 and withdrawing>0;";
	$result=$dbcon->query($qr);
	
	

	for($i=0; $i<($result->num_rows);$i++){
		
		$userdata=$result->fetch_array();
		
		echo ($userdata['name']." : ".number_format($userdata['withdrawing'])." : <a href='./removewd.php?id=".$userdata['id']."&amount=".$userdata['withdrawing']."'>Remove</a><br><br>\n");
	}
}


?>