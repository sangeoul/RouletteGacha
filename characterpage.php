
<script language=javascript>
function unregisterpopup(){


	var popupurl = "./unregisterpopup.html";	//탈퇴팝업창주소

	var popupOptions = "width=400, height=500, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, status=yes;";    //팝업창 옵션

		window.open(popupurl,"",popupOptions);


}

function walletreload(){

	var popupurl = "./walletread.php";	//Wallet 리로드 주소

	var popupOptions = "width=600, height=150, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=no, status=no;";    //팝업창 옵션
		window.open(popupurl,"walletreload",popupOptions);
}

function withdraw(){

	var popupurl = "./requestwithdraw.php";	//Wallet withdraw

	var popupOptions = "width=600, height=150, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=no, status=no;";    //팝업창 옵션
		window.open(popupurl,"withdraw",popupOptions);
}

function movetoroulette(num){
	window.location.href="./roulette.php?number="+num;
}


function attend_today(){

	var popupurl = "./attendance_check.php";	//출첵 주소

	var popupOptions = "width=600, height=150, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=no, status=no;";    //팝업창 옵션
	window.open(popupurl,"attend",popupOptions);
}


document.getElementById
</script>
<?php



include 'phplib.php';
dbset();

logincheck();

echo "<html>\n<head>\n<title>".getUserName($_GET['id'])."'s page</title>\n<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'></head>";

echo "<body>\n";

if(intval($_GET['id'])==intval($_SESSION['roulette_roulette_user_id'])) {

	echo("<script>location.replace('./mypage.php');</script>");
}

menutable();


$qr="select * from accounts where id=".$_GET['id']." and active=1;";

$result=$dbcon->query($qr);

if($result->num_rows==0){
	errorback("존재하지 않는 이용자입니다.");
}

$characterdata=$result->fetch_array();

echo "<br><table border=1><tr><td rowspan=5 class='mypageport'><img src='".getUserPortrait($characterdata['id'],200)."' width=200></td>";

echo "<td class='mypagename'>".getUserName($characterdata['id'])." </td></tr>";

echo "<tr><td class='mypagebalance'>Balance : ".number_format($characterdata['balance'])." K  </td></tr>";

echo "<tr><td class='mypage3'></td></tr>";

echo "<tr><td class='mypage4'>";
echo "<a href='./collection.php?id=".$characterdata['id']."'>[Collections]</a>";
echo "</td></tr>";

echo "<tr><td class='mypage5'></td></tr></table><hr>";


?>

</body></html>
