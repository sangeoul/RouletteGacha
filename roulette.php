<html>
<head>
<title>Capsuleer's Roulette</title>
<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-7625490600882004",
          enable_page_level_ads: true
     });
</script>
</head>


<?php



include 'phplib.php';

dbset();
logincheck();
menutable();

$CHARACTERS_PER_ROW=2;
$HEIGHT_PER_ROW=50;
$WIDTH_PER_COL=150;

$WINNER_COLOR="#80B0E0";


if(!isset($_GET['number'])){

	errorback("잘못된 접근입니다.");

}

	echo"<body>\n";




$qr="select * from roulette where number=".$_GET['number'];

$result=$dbcon->query($qr);

$gamedata=$result->fetch_array();


$members_list=explode(",",$gamedata['members_list']);

for($i=0;$i<sizeof($members_list);$i++){
	$members_list[$i]=intval($members_list[$i]);
}

//티켓이 다 팔렸다면 완료시킨다.
if($gamedata['sold']==sizeof($members_list) && $gamedata['iscompleted']==0){
	
	$chickendinner=rand(0,($gamedata['sold']-1));

	$qr1="update roulette set winner=".$chickendinner.", winnerid=".$members_list[$chickendinner]." , iscompleted=1, finishtime=UTC_TIMESTAMP  where number=".$_GET['number']." AND iscompleted=0;";

	$gamedata['winnerid']=$members_list[$chickendinner];

	//상품이 이스크일 경우에는 즉시 Balance 에 적용시켜준다.
	if($gamedata['isprizeisk']==1){

		$qrb="select balance from accounts where active=1 and id=".$members_list[$chickendinner].";";
		$result=$dbcon->query($qrb);
		$balance=$result->fetch_array();

		//transactions 에 기입하는 쿼리 $qr1
		$qr="select id from transactions where userid=".$members_list[$chickendinner]." order by id desc";
		if(!($dbcon->query($qr))){

			errorclose("에러 발생 . DB Error 3");
		}
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=$topid[0]+1;

		$qr2="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$members_list[$chickendinner].",'".getUserName($members_list[$chickendinner])."',".(intval($gamedata['prize'])*1000000).",".($balance[0]+(intval($gamedata['prize'])*1000000)).",UTC_TIMESTAMP,'당첨');";

		//유저의 balance를 적용시켜는 쿼리 $qr2
		$qr3="update accounts set balance=balance+".(intval($gamedata['prize'])*1000000)." where id=".$members_list[$chickendinner]." and active=1;";


		if(!($dbcon->query($qr1)&&$dbcon->query($qr2)&&$dbcon->query($qr3))){
			errorhome("상금 전달에 문제가 발생하였습니다. DB Error");
		}		
		else{
			echo ("<script>window.location.reload();</script>");
		}

	}
	


}



$rouletteinfo="";

if($gamedata['iscompleted']){
	$rouletteinfo="이 룰렛은 완료되었습니다.<br> 시작한 사람 : ".getUserName($gamedata['starter'])."<br>\n".$gamedata['starttime']."~".$gamedata['finishtime'];


	$rouletteinfo=$rouletteinfo."<br>\n";
}
else{
	$rouletteinfo="이 룰렛은 현재 진행중입니다.<br> 시작한 사람 : ".getUserName($gamedata['starter']);
		
	if($gamedata['starter']==$_SESSION['roulette_user_id'] && $gamedata[sold]==1){
		$rouletteinfo=$rouletteinfo."<a class='cancelroulette' href='javascript:cancelroulette(".$_GET['number'].")'>[Cancel Roulette]</a>";
	}
	
	$rouletteinfo=$rouletteinfo."<br>\n".$gamedata['starttime']."~".$gamedata['finishtime']."<br>\n";

	if($gamedata['duplication_allowed']==0){
		$rouletteinfo=$rouletteinfo."<font color=red>이 룰렛은 중복 참여가 불가능합니다.</font><br>\n";
	}
	
}


//테이블 만드는 반복문

$roulettetable="<table border=1>";

$roulettetable=$roulettetable."<tr><td colspan=".($CHARACTERS_PER_ROW*2)." class='rouletteinfo'>".$rouletteinfo."</td></tr>";

if($gamedata['iscompleted']==1){
	$roulettetable=$roulettetable."<tr height=".($HEIGHT_PER_ROW-20)."><td colspan=".($CHARACTERS_PER_ROW*2)." align=center><font size=6> Winner : ".getUserName($members_list[$gamedata['winner']])."</font></td></tr>";
}

$roulettetable = $roulettetable."<tr height=".$HEIGHT_PER_ROW."><td colspan=".($CHARACTERS_PER_ROW*2)." class='roulette_title'><span class='roulette_title_prize'>Prize : ".$gamedata['prize']."</span><span class='roulette_title_ticket'>ticket : ".number_format($gamedata['ticket_price'])." mil K</span></td></tr>";





$roulettetable=$roulettetable."<tr height=".$HEIGHT_PER_ROW.">";
$portrait=null;
$namemenu=null;

for($i=0,$nextline=0;$i<$gamedata['members_number'];$i++,$nextline++){

	if($nextline==$CHARACTERS_PER_ROW){
		$nextline=0;
		$roulettetable=$roulettetable."</tr><tr height=".$HEIGHT_PER_ROW.">";
	}

	//포트레잇 작성
	if($members_list[$i]==0){
		$portrait="<img src='./images/ticket.jpg'>";
	}
	else{
		$portrait="<img src='".getUserPortrait($members_list[$i],49)."'>";
	}

	//닉네임 혹은 티켓신청 작성

	if($members_list[$i]==0){

		$namemenu="<a href='javascript:buyticket(".$_GET['number'].",".$i.");'>티켓 구매</a>";
	}
	else{
		$namemenu="<a href='javascript:moveto_userpage(".$members_list[$i].")'>".getUserName($members_list[$i])."</a>";

	}
	$addstring="<td width=".$HEIGHT_PER_ROW." id='port".$i."' class='roulette_portrait'>".$portrait."</td><td width=".$WIDTH_PER_COL." id='name".$i."' class='roulette_name'>".$namemenu."</td>";
	$roulettetable=$roulettetable.$addstring;

}
$roulettetable=$roulettetable."</tr></table>";

$roulettestyle= "<script>document.getElementById('port".$gamedata['winner']."').style.backgroundColor='".$WINNER_COLOR."';\n document.getElementById('name".$gamedata['winner']."').style.backgroundColor='".$WINNER_COLOR."';</script>";


//echo $rouletteinfo."<br>\n";
echo $roulettetable;
echo $roulettestyle;
echo "</body>\n";


//buyticket 팝업 연결 함수 작성.
echo "<script language=javascript>\n\n";

echo "function buyticket(rn,tn){\n\n";

echo "var buyconf=window.confirm(''+(tn+1)+'번 티켓입니다. 가격은 ".$gamedata['ticket_price']."mil K 입니다. \\n티켓을 구매하시겠습니까?');\n";

//echo "/*\n";
echo "if(buyconf){\n var popupurl='./rouletteticket.php?rn='+rn+'&tn='+tn;\n";


echo "var popupOptions = 'width=200, height=200, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=no, status=yes;';\n";

echo " window.open(popupurl,'ticketpopup',popupOptions);\n} \n\n";

//echo "*/";


echo "\n}";




echo "</script>";
?>

<script>
function cancelroulette(rn){


	var popupurl = "./cancelroulette.php?rn="+rn;	//탈퇴팝업창주소

	var popupOptions = "width=400, height=500, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, status=yes;";    //팝업창 옵션

	
	if(confirm("정말로 룰렛을 취소하시겠습니까?")){
		window.open(popupurl,"cancelroulette",popupOptions);
	}


}
</script>

