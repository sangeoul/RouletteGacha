
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

echo "<html>\n<head>\n<title>".$_SESSION['roulette_user_name']."'s mypage</title>\n<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'></head>";

echo "<body>\n";

menutable();

$attendance_button="";
$qr="select * from attendance where id=".$_SESSION['roulette_user_id']." and date(date_add(attendance_date, interval -11 hour))=date(date_add(UTC_TIMESTAMP, interval -11 hour));";
	
	$result=$dbcon->query($qr);

	$attendance=$result->fetch_array();

if($result->num_rows == 0) {

	$attendance_button="<img id='attendance_button' class='attendance_button' onclick='javascript:attend_today();'></img>";
}



$qr="select * from accounts where id=".$_SESSION['roulette_user_id']." and active=1;";

$result=$dbcon->query($qr);

$characterdata=$result->fetch_array();

echo "<br><table border=1><tr><td rowspan=5 class='mypageport'><img src='".getUserPortrait($_SESSION['roulette_user_id'],200)."' width=200></td>";

echo "<td class='mypagename'>".getUserName($_SESSION['roulette_user_id']).$attendance_button." </td></tr>";

echo "<tr><td class='mypagebalance'>Balance : ".number_format($characterdata['balance'])." K  <img src='./images/loadlight.png' class='mypageloadbutton' onclick='javascript:walletreload()' id='loadbutton'> <span id='cooldowntimer' name='cooldowntimer class='cooldowntimer' onclick='javascript:timers()'></span> </td></tr>";

echo "<tr><td class='mypage3'><!--<a href='javascript:withdraw()'> [출금하기] </a> --> <a href='./transaction_history.php' style='margin-left:30px'> [거래내역보기] </a></td></tr>";

echo "<tr><td class='mypage4'>";
echo "<a href='./collection.php'>[My Collections]</a>";
echo "</td></tr>";

echo "<tr><td class='mypage5'><table><tr><td class='logout' onclick='javascript:document.location.replace(\"/logout.php\")'>LOG OUT</td></a><td width=50></td><td class='removeaccount' onclick='javascript:unregisterpopup();'> REMOVE ACCOUNT</table></td></tr></table><hr>";


$qr="select * from roulette where iscompleted=0 AND match(members_list) against ('".$_SESSION['roulette_user_id']."' in boolean mode) limit 5;";

$result=$dbcon->query($qr);


echo "<table><tr><td colspan=4 align=center class='maintitle'><a href='myroulettes.php?scope=current'>Current My Active Roulette : ".$result->num_rows."</a></td></tr>";


for($i=0;$i<($result->num_rows);$i++){

	$roulette=$result->fetch_array();

echo "<tr class='roulettelist'><td class='roulettelist_name' onclick='javascript:movetoroulette(".$roulette['number'].");'>Prize : ".$roulette['prize']."</td><td class='roulettelist_members'>(".$roulette['sold']."/".$roulette['members_number'].")</td><td class='roulettelist_price'>Ticket : ".$roulette['ticket_price']." mil K</td><td class='roulettelist_time'>".$roulette['starttime']." ~</td></tr>";	

}

echo ("</table>");

$qr="select latest_loaded from accounts where active=1 and id=".$_SESSION['roulette_user_id'].";";

$result=$dbcon->query($qr);
$ltime=$result->fetch_array();


echo (" <script>\n var ltime=new Date('".$ltime[0]."').getTime();\n ltime+=((".$WALLET_COOLDOWN."*1000)+(3600*9*1000));\n var wc=".$WALLET_COOLDOWN.";\n</script>");
?>
<br>

<script>
/*
function cooldown(){
	
	var ctime= new Date().getTime();
	
	if(ltime<ctime){
		document.getElementById('loadbutton').src='./images/loadlight.png';*/
		document.getElementById('loadbutton').onclick=function(){walletreload();}
/*		document.getElementById('cooldowntimer').innerHTML=("쿨다운 : "+wc+"초");
	}
	else {
		var sss=Math.floor((ltime-ctime)/1000);
		var hhh=Math.floor(sss/3600);
		var mmm=Math.floor((sss%3600)/60);
		var sss=Math.floor(sss%60);
		
		var timestring="[";
		if(hhh>0){
			hhh=("00"+hhh).slice(-2)+":";
		}
		else{hhh=""}
		if(hhh>0 || mmm>0){
			mmm=("00"+mmm).slice(-2)+":";
			
		}
		else{mmm=""}
		sss=(("00"+sss).slice(-2));
		
		document.getElementById('loadbutton').src='./images/loaddark.png';
		document.getElementById('loadbutton').onclick=function(){alert('Cooldown');}
		document.getElementById('cooldowntimer').innerHTML=hhh+mmm+sss;
		setTimeout(function(){cooldown();},1000);

	}
}
cooldown();
*/
</script>
</body></html>
