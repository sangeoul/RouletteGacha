
<html>
	<head><title>Capsuleer's Roulette</title>
	
	<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
	

<script language=javascript>

	function roulette(){
		var number;
		var winner;
		var winnerid;
		var winnername;
		var ticket_price;
		var prize;
		var prizeimg;
		var isprizeisk;
		var iscompleted;
		var starttime;
		var finishtime;
		var sold;
		var members_number;
		var members_list;

	}
	var roulettes= new Array();
	var roulettes_f= new Array();

	var activesum=0;
	var finishedsum=0;

</script>
	
	</head>

<body>
<?php

include 'phplib.php';
dbset();
logincheck();
menutable();

echo "<script language=javascript>\n";
//진행중인 게임 정보들을 받아온다.
$qr = "select * from roulette where iscompleted=0 AND match(members_list) against ('".$_SESSION['roulette_user_id']."' in boolean mode) order by starttime desc limit 2000;";

$result=$dbcon->query($qr);


for($i=0;$i<$result->num_rows;$i++){
	
	$games[$i]=$result->fetch_array();
	echo "roulettes[".$i."]=new roulette();\n";
	
	echo "roulettes[".$i."].number=".$games[$i]['number'].";\n";
	//echo "roulettes[".$i."].winner=".$games[$i]['winner'].";\n";
	echo "roulettes[".$i."].ticket_price=".$games[$i]['ticket_price'].";\n";
	echo "roulettes[".$i."].prize='".$games[$i]['prize']."';\n";
	echo "roulettes[".$i."].prizeimg='".$games[$i]['prizeimg']."';\n";
	echo "roulettes[".$i."].isprizeisk=".$games[$i]['isprizeisk'].";\n";
	echo "roulettes[".$i."].iscompleted=".$games[$i]['iscompleted'].";\n";
	echo "roulettes[".$i."].starttime='".$games[$i]['starttime']."';\n";
	//echo "roulettes[".$i."].finishtime='".$games[$i]['finishtime']."';\n";
	echo "roulettes[".$i."].members_number=".$games[$i]['members_number'].";\n";
	echo "roulettes[".$i."].sold=".$games[$i]['sold'].";\n";
	echo "roulettes[".$i."].members_list='".$games[$i]['members_list']."';\n";
	
	echo "activesum=activesum+".intval($games[$i]['prize']).";";

	
}


//완료된 게임 정보들을 받아온다.
$qr = "select * from roulette where iscompleted=1 AND match(members_list) against ('".$_SESSION['roulette_user_id']."' in boolean mode) order by finishtime desc limit 2000;";
$result=$dbcon->query($qr);
for($i=0;$i<$result->num_rows;$i++){

	$finishedgames[$i]=$result->fetch_array();
	echo "roulettes_f[".$i."]=new roulette();\n";

	echo "roulettes_f[".$i."].number=".$finishedgames[$i]['number'].";\n";
	echo "roulettes_f[".$i."].winner=".$finishedgames[$i]['winner'].";\n";
	echo "roulettes_f[".$i."].winnerid=".$finishedgames[$i]['winnerid'].";\n";
	echo "roulettes_f[".$i."].winnername='".getUserName($finishedgames[$i]['winnerid'])."';\n";
	echo "roulettes_f[".$i."].ticket_price=".$finishedgames[$i]['ticket_price'].";\n";
	echo "roulettes_f[".$i."].prize='".$finishedgames[$i]['prize']."';\n";
	echo "roulettes_f[".$i."].prizeimg='".$finishedgames[$i]['prizeimg']."';\n";
	echo "roulettes_f[".$i."].isprizeisk=".$finishedgames[$i]['isprizeisk'].";\n";
	echo "roulettes_f[".$i."].iscompleted=".$finishedgames[$i]['iscompleted'].";\n";
	echo "roulettes_f[".$i."].starttime='".$finishedgames[$i]['starttime']."';\n";
	echo "roulettes_f[".$i."].finishtime='".$finishedgames[$i]['finishtime']."';\n";
	echo "roulettes_f[".$i."].members_number=".$finishedgames[$i]['members_number'].";\n";
	echo "roulettes_f[".$i."].sold=".$finishedgames[$i]['sold'].";\n";
	echo "roulettes_f[".$i."].members_list='".$finishedgames[$i]['members_list']."';\n";

	echo "finishedsum=finishedsum+".intval($finishedgames[$i]['prize']).";";
}


echo "</script>";



?>

<script language='javascript'>

function movetoroulette(num){
	window.location.href="./roulette.php?number="+num;
}


document.writeln("<table width=650><th class='maintitle'>>>내가 참가한 룰렛<<<span></td></table><hr><table class='listtable'><tr><th class='maintitle'>진행중인 캡슐리어 룰렛 : "+roulettes.length+"</th><td class='balancesum'> 총 "+Number(activesum).toLocaleString('en')+",000,000 K</td></tr>");

document.writeln("<tr><td align=center colspan=2><span id='activerouletteindex' class='indexnumbers'></tr></td>");	//상단 인덱스 배치
document.writeln("<tr><td align=center colspan=2><span id='activeroulettelist'></tr></td>");	//리스트 배치
document.writeln("</table><hr style='margin-top:120px'>");
changeindex(1,'active');

		
document.writeln("<table class='listtable'><tr><th class='maintitle'>완료된 캡슐리어 룰렛 : "+roulettes_f.length+"</th><td class='balancesum'> 총 "+Number(finishedsum).toLocaleString('en')+",000,000 K</td></tr>");

document.writeln("<tr><td align=center colspan=2><span id='finishedrouletteindex'></tr></td>");	//상단 인덱스 배치
document.writeln("<tr><td align=center colspan=2><span id='finishedroulettelist'></tr></td>");		//리스트 배치
document.writeln("</table><hr>");
changeindex(1,'finished');

//인덱스 만드는것이 복잡하므로 따로 함수로 떼어낸다.
function make_index_string(currentindex,maxindex,type){

	var startindex;
	
	//인덱스 시작번호를 결정한다.
	if(currentindex<6) startindex=1;
	else if(currentindex>maxindex-5) startindex=maxindex-10;
	else if(currentindex>=6) startindex=currentindex-5;
	
	var indexstring="<table><tr><td class='listindex' align=center>";
	//인덱스는 11개씩 출력한다
	
	for(var i=startindex; i<startindex+11 && i<=maxindex ;i++ ){
		if(i!=currentindex)
			indexstring=indexstring+"<a class='listindex' id='idx"+type+i+"' href='javascript:changeindex("+i+",\""+type+"\")'>"+i+"</a>";
		else{
			indexstring=indexstring+"<a class='listindex_selected' id='idx"+type+i+"' >"+i+"</a>";
		}
	}

	indexstring=indexstring+"</td></tr></table>";

	document.getElementById(type+'rouletteindex').innerHTML=indexstring;
	
	
}

//인덱스를 바꾸는 것도 함수로 처리한다.
function changeindex(selectedindex,type){

	var liststring="";
	//진행중인 룰렛 스트링
	if(type=="active"){

		var startn1=(selectedindex-1)*10;
		
		
		for( var i=startn1; i<(startn1 + 10) && i<roulettes.length; i++){
			liststring=liststring+"<table border=0><tr class='roulettelist'><td class='roulettelist_name' onclick='javascript:movetoroulette("+roulettes[i].number+");'>Prize : "+roulettes[i].prize+"</td><td class='roulettelist_members'>("+roulettes[i].sold+"/"+roulettes[i].members_number+")</td><td class='roulettelist_price'>Ticket : "+roulettes[i].ticket_price+" mil K</td><td class='roulettelist_time'>"+roulettes[i].starttime+" ~</td></tr></table>";
		}
		document.getElementById('activeroulettelist').innerHTML=liststring;
		make_index_string(selectedindex,Math.ceil(roulettes.length/10),"active");
		
	}

	//완료된 룰렛 스트링
	else if (type=="finished"){
		
		var startn2=(selectedindex-1)*10;

		
		for( var i=startn2; i<(startn2+10) && i<roulettes_f.length; i++){

			<?php
		echo ("var nameclass ='roulettelist_winner';\n");
		echo ("if(roulettes_f[i].winnerid==".$_SESSION['roulette_user_id'].") nameclass='roulettelist_winner_me';");

?>	
			liststring=liststring+"<table border=0><tr class='roulettelist'><td class='roulettelist_name' onclick='javascript:movetoroulette("+roulettes_f[i].number+");'>Prize : "+roulettes_f[i].prize+"</td><td class="+nameclass+">"+roulettes_f[i].winnername+"</td><td class='roulettelist_time'>~ "+roulettes_f[i].finishtime+"</td></tr></table>";
		}
		document.getElementById('finishedroulettelist').innerHTML=liststring;
		make_index_string(selectedindex,Math.ceil(roulettes_f.length/10),"finished");
	}


}




</script>

</body>
</html>