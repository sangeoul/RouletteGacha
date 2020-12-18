<html>
<head>
<title>Main Page</title>
<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'>

<script language=javascript>


function movetoroulette(num){
	window.location.href="./roulette.php?number="+num;
}

document.getElementById
</script>

</head>



<body>

<?php

include 'phplib.php';
dbset();

logincheck();


menutable();

//Recent Roulette====================================================================================

$qr="select * from roulette where iscompleted=0 order by starttime desc limit ".$MAINPAGE_RECENT_ROULETTES_LINE.";";

$result=$dbcon->query($qr);

echo ("<table><tr><td colspan=4 align=center class='maintitle'><a href='roulettelist.php'>Recent ".$result->num_rows." Roulettes </a></td></tr>");


for($i=0;$i<($result->num_rows);$i++){

	$roulette=$result->fetch_array();

echo "<tr class='roulettelist'><td class='roulettelist_name' onclick='javascript:movetoroulette(".$roulette['number'].");'>Prize : ".$roulette['prize']."</td><td class='roulettelist_members'>(".$roulette['sold']."/".$roulette['members_number'].")</td><td class='roulettelist_price'>Ticket : ".$roulette['ticket_price']." mil K</td><td class='roulettelist_time'>".$roulette['starttime']." ~</td></tr>";	

}

echo ("</table><hr>");

//Recent Roulette End============================================================


//Biggest Winner ===========================================


//유저리스트를 불러온다
$qr="select * from accounts where active=1";
$result=$dbcon->query($qr);

$winvalue=array();

//유저리스트를 이용해 지난 $MAINPAGE_BIGGESTWINNER_TIMELIMIT 시간 동안의 당첨금 합을 계산해서 $winvalue 에 담는다.
for($i=0;$i<$result->num_rows;$i++){
	$userdata=$result->fetch_array();

	$qr2="select sum(cast(prize as unsigned)) from roulette where iscompleted=1 and isprizeisk=1 and winnerid=".$userdata['id']." and timestampdiff(hour, finishtime, UTC_TIMESTAMP)<".$MAINPAGE_BIGGESTWINNER_TIMELIMIT.";";
	$result2=$dbcon->query($qr2);
	$usersum=$result2->fetch_row();
	$winvalue[$i][0]=$userdata['id'];
	$winvalue[$i][1]=$usersum[0];
}

//winvalue 를 정렬한다 (내림차순)
for($i=0;$i<sizeof($winvalue);$i++){
	for($j=($i+1);$j<sizeof($winvalue);$j++){
		
		if($winvalue[$i][1]<$winvalue[$j][1]){
			$tn=$winvalue[$i][0];
			$tv=$winvalue[$i][1];

			$winvalue[$i][0]=$winvalue[$j][0];
			$winvalue[$i][1]=$winvalue[$j][1];

			$winvalue[$j][0]=$tn;
			$winvalue[$j][1]=$tv;

		}

	}	
}	//정렬끝



echo("<table><tr><th class='maintitle' colspan=4 ><a href='./ranking.php?scope=winner&timelimit=".$MAINPAGE_BIGGESTWINNER_TIMELIMIT."'>BIGGEST WINNER RANK (last ".$MAINPAGE_BIGGESTWINNER_TIMELIMIT."h)</a></th></tr>");
for($i=0;$i<sizeof($winvalue) && $i< $MAINPAGE_BIGGESTWINNER_RANKING_LINE;$i++){
	
	$qr="select * from accounts where active=1 and id=".$winvalue[$i][0].";";
	$result=$dbcon->query($qr);
	$bigwinner=$result->fetch_array();
	echo("<tr>\n");
	echo("<td class='winner_ranking_number' id='rank".($i+1)."'>".($i+1)."</td>\n");
	echo("<td class='winner_ranking_portrait' id='rank".($i+1)."'><img src='".getUserPortrait($bigwinner['id'],30)."' class='ranking_portrait' id='rank".($i+1)."'></img></td>\n");
	echo("<td class='winner_ranking_name' id='rank".($i+1)."' ><a href='javascript:moveto_userpage(".$bigwinner['id'].")'>".getUserName($bigwinner['id'])."</a></td>\n");
	echo("<td class='winner_ranking_balance' id='rank".($i+1)."'>".number_format($winvalue[$i][1])." mil K</td>\n");
	echo("</tr>\n");
}
echo("</table><hr>");





//Biggest Winner End======================================

// Collection Rank ============================================
	

	$collecting_user;

	$qr="select id from items where active=1;";

	$result=$dbcon->query($qr);
	$allnumber=$result->num_rows;

	$qr="select id from accounts where active=1;";
	$result=$dbcon->query($qr);
	for($i=0;$i<($result->num_rows);$i++){
			
		$parsed=$result->fetch_row();
		$collecting_user[$i][0]=$parsed[0];

		$point=$dbcon->query("select id from collections where active=1 and userid=".$collecting_user[$i][0].";");
		$collecting_user[$i][1]=$point->num_rows;
	}

	//$collecting_user 를 정렬한다 (내림차순)
	for($i=0;$i<sizeof($collecting_user);$i++){
		for($j=($i+1);$j<sizeof($collecting_user);$j++){
		
			if($collecting_user[$i][1]<$collecting_user[$j][1]){
				$tn=$collecting_user[$i][0];
				$tv=$collecting_user[$i][1];

				$collecting_user[$i][0]=$collecting_user[$j][0];
				$collecting_user[$i][1]=$collecting_user[$j][1];

				$collecting_user[$j][0]=$tn;
				$collecting_user[$j][1]=$tv;

			}

		}	
	}	//정렬끝


	echo("<table><tr><th class='maintitle' colspan=4 ><a href='./ranking.php?scope=collection'>Collection Rank</a></th></tr>");
	for($i=0;$i<sizeof($collecting_user) && $i<$MAINPAGE_COLLECTION_RANKING_LINE;$i++){
		echo("<tr>\n");
		echo("<td class='winner_ranking_number' id='rank".($i+1)."'>".($i+1)."</td>\n");
		echo("<td class='winner_ranking_portrait' id='rank".($i+1)."'><img src='".getUserPortrait($collecting_user[$i][0],30)."' class='ranking_portrait' id='rank".($i+1)."'></img></td>\n");
		echo("<td class='winner_ranking_name' id='rank".($i+1)."'><a href='javascript:moveto_userpage(".$collecting_user[$i][0].")'>".getUserName($collecting_user[$i][0])."</a></td>\n");
		echo("<td class='winner_ranking_balance' id='rank".($i+1)."'>".number_format($collecting_user[$i][1])." / ".$allnumber."</td>\n");
		echo("</tr>\n");
	}
	echo("</table><hr>");

//Collection Rank End==============================


// Rich King Rank ============================================
$qr="select * from accounts where active=1 order by balance desc limit ".$MAINPAGE_RICHKING_RANKING_LINE.";";

$result=$dbcon->query($qr);

echo("<table><tr><th class='maintitle' colspan=4 ><a href='./ranking.php?scope=rich'>Rich King Rank</a></th></tr>");
for($i=0;$i<$result->num_rows;$i++){
	
	$richking=$result->fetch_array();

	echo("<tr>");
	echo("<td class='rich_ranking_number' id='rank".($i+1)."'>".($i+1)."</td>");
	echo("<td class='rich_ranking_portrait' id='rank".($i+1)."'><img src='".getUserPortrait($richking['id'],60)."' class='ranking_portrait'  id='rank".($i+1)."'></img></td>");
	echo("<td class='rich_ranking_name' id='rank".($i+1)."'><a href='javascript:moveto_userpage(".$richking['id'].")'>".$richking['name']."</a></td>");
	echo("<td class='rich_ranking_balance' id='rank".($i+1)."'>".number_format($richking['balance'])." K</td>");
	echo("</tr>");
}
echo("</table><hr>");

//Rich king Rank End==============================================





?>
<br>

</body></html>
