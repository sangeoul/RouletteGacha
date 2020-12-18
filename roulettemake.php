<?php

include 'phplib.php';
dbset();
logincheck();



/*
$qr="select * from roulette where starter=".$_SESSION['roulette_user_id']." and iscompleted=0 ;";
$result=$dbcon->query($qr);
if(($result->num_rows)>=$MAX_ACTIVE_ROULETTES_PER_USER){
	errorclose("룰렛은 한 번에 하나만 만들 수 있습니다.");
}
*/

$qr="select * from roulette where iscompleted=0 ;";
$result=$dbcon->query($qr);
if(($result->num_rows)>=$MAX_ACTIVE_ROULETTES){
	errorclose("현재 활성화 된 룰렛이 최대치입니다.(총 ".$MAX_ACTIVE_ROULETTES." 개)");
}

$qr="select * from roulette where starter=".$_SESSION['roulette_user_id']." and iscompleted=0;";
$result=$dbcon->query($qr);
if(($result->num_rows)>=$MAX_ACTIVE_ROULETTES_PER_USER){
	errorclose("현재 활성화 된 룰렛이 최대치입니다.(1인당 ".$MAX_ACTIVE_ROULETTES_PER_USER." 개)");
}

?>

<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">

<table border=1>
	<form action='./makegame.php' method='post'>
	<tr height=50><td colspan=2 align=center class='roulettemake_title'> Make New Loulette!!</font> </td></tr>
	<tr height=30><td width=200 class='roulettemake_content'>티켓 가격</td><td width=250 class='roulettemake_content'>
	<input type=number id='price' name='price' min=1 onfocusout='javascript:calcall();'></input>
	mil K</td></tr>
	<tr height=30><td class='roulettemake_content'>참가자 수 (최대 <?php echo $MAX_ROULETTES_MEMBERS ?>명)</td><td class='roulettemake_content'>
	<input type=number id='membersnumber' name='membersnumber' min=2 max=<?php echo $MAX_ROULETTES_MEMBERS ?> onfocusout='javascript:calcall();'> 명</input>
	</td></tr>
	<tr height=30><td class='roulettemake_content'>내가 참가할 번호</td><td class='roulettemake_content'>
	<input type=number id='mynumber' name='mynumber' min=1 max=<?php echo $MAX_ROULETTES_MEMBERS ?> onfocusout='javascript:calcall();'> 번</input>
	</td></tr>
	<tr height=40><td class='roulettemake_content'>상금</td><td  class='roulettemake_content' style='background: #CCCCCC;'><span class='prizestyle'><span id='prize'>0</span> mil K</span></td></tr>
	<tr height=30><td class='roulettemake_content'>중복 참여 금지?</td><td class='roulettemake_content'><font color=red>금지</font>
	<input type='checkbox' id='duplication' name='duplication' value=1 onfocusout='javascript:calcall();'></input>
	</td></tr>
	<tr height=30><td class='roulettemake_content'></td><td class='roulettemake_content'>
	<input type=submit value='만들기'></input>
	</td></tr>
	<tr height=30><td class='roulettemake_content'></td><td class='roulettemake_content'><a href='javascript:close();' >닫기</a></td></tr>
	<input type=hidden id='gametype' name='gametype' value='roulette'></input>
	<input type=hidden id='userid' name='userid' value=<?php echo($_SESSION['roulette_user_id']);?> ></input>
	</form>
</table>

<script>

function calcall(){


	
	var maxmembers=<?php echo $MAX_ROULETTES_MEMBERS ?>;
	var price=parseInt(document.getElementById('price').value);
	var membersnumber=parseInt(document.getElementById('membersnumber').value);
	var mynumber=parseInt(document.getElementById('mynumber').value);
	var prize;


	if(price<1){
		price=1;
	}

	if(membersnumber<2){
		membersnumber=2;
	}
	else if(membersnumber>maxmembers){
		membersnumber=maxmembers;
	}
	if(mynumber<1){
		mynumber=1;
	}
	if(mynumber>membersnumber){
		mynumber=membersnumber;
	}
	
	prize=Math.floor(price*membersnumber*<?php echo $FEE_APPLIED; ?>);


	document.getElementById('price').value=price;
	document.getElementById('membersnumber').value=membersnumber;
	document.getElementById('mynumber').value=mynumber;
	document.getElementById('prize').innerHTML=prize;


}

</script>
