<canvas id='hackingboard' width=400 height=300 ></canvas><br>
<!--<span id='offsetx'></span> : <span id='offsety'></span><br>-->

<?php



function openbox( $bstring){

	$atari=explode(",",$bstring);

	for($i=0;$i<6;$i++){
		$atari[$i]=intval($atari[$i]);
	}

	return $atari;
}



//가챠를 뽑아주는 함수.
function pickup(){

	global $dbcon;
	global $GACHARATE;

	$result=$dbcon->query("select * from items where active=1 order by price asc;");


	$data;
	$sumof=0;
	$per=array();

	for($i=0;$i<$result->num_rows;$i++){
	
		$items=$result->fetch_array();
		$data[$i]['id']=$items['id'];
		$data[$i]['price']=$items['price'];

		$per[$i]=pow($data[$i]['price'],$GACHARATE);
		$sumof+=$per[$i];	
	}

	$randnumber=(mt_rand(0,100000000)/100000000);
	$getnumber=0;
	for($i=0;$i<$result->num_rows;$i++){

		//당첨이면 for문 탈출
		if( $randnumber < ($per[$i]/$sumof)){
			$getnumber=$data[$i]['id'];
			break;
		}
		
		else{
			$randnumber-=($per[$i]/$sumof);
		}
	}

	//숫자가 너무 커서 당첨이 아닌 것처럼 나왔을 때의 예외처리
	if($getnumber==0){
		$getnumber=$data[(sizeof($data)-1)]['id'];
	}

	return $getnumber;
}


include "phplib.php";
dbset();
logincheck();


$result=$dbcon->query("select * from accounts where active=1 and id=".$_SESSION['roulette_user_id'].";");
$userdata=$result->fetch_array();


$atari=array();
$errorv=false;

if($userdata['lastgacha']=='0'){

	//원래가챠가 없었으면 새로 가챠를 뽑는다

	//먼저 월렛 점검
	if($userdata['balance']<($GACHABOX_PRICE*1000000)){
		
		$errorv=true;
		errorclose("잔고가 부족합니다.");
	}

	//월렛이 충분하면 차감 DB 불러올 때 점검 한 번 더.
	else{

		if(!($dbcon->query("update accounts set balance=(balance-".($GACHABOX_PRICE*1000000).") where active=1 and id=".$_SESSION['roulette_user_id'].";"))) {
			$errorv=true;
			errorclose("DB Error 1");
		}

		//월렛 문제가 없으면 뽑기를 뽑는다.
		else{
			//트랜잭션에서 $trid 구해오기.
		$qr="select id from transactions where userid=".$_SESSION['roulette_user_id']." order by id desc;";
		$result=$dbcon->query($qr);
		$topid=$result->fetch_array();
		$trid=($topid[0]+1);

		$qr3="insert into transactions (id,userid,name,amount,afterbalance,transaction_date,transaction_type) values (".$trid.",".$_SESSION['roulette_user_id'].",'".getUserName($_SESSION['roulette_user_id'])."',".($GACHABOX_PRICE*1000000).",".($userdata['balance']-($GACHABOX_PRICE*1000000)).",UTC_TIMESTAMP,'뽑기');";

		$dbcon->query($qr3);

			for($i=0;$i<6;$i++){
				$atari[$i]=pickup();
			}

			//유저 뽑기내용은 바로 바꿔준다.
			$boxstring=$atari[0].",".$atari[1].",".$atari[2].",".$atari[3].",".$atari[4].",".$atari[5];
	
			if(!($dbcon->query("update accounts set lastgacha='".$boxstring."' where active=1 and id=".$_SESSION['roulette_user_id'].";"))){
				$errorv=true;
				errorclose("DB Error 2");
			}



		}


	}

}
//하다 만 것이 있었으면 속행
else{
		echo("<script>alert('뽑기를 완료하지 않은 상자가 남아 있습니다.');</script>");
		$atari=openbox($userdata['lastgacha']);
}



//뽑든 속행이든 그대로 진행한다.

if(!$errerv){

echo("<script>window.opener.location.reload();</script>");
$shipdata=array();

for($i=0;$i<6;$i++){

	$result=$dbcon->query("select * from items where id=".$atari[$i]." and active=1");

	$shipdata[$i]=$result->fetch_array();

	//echo($shipdata[$i]['name']."<br>\n");
	echo("<img src='https://imageserver.eveonline.com/Render/".$atari[$i]."_128.png' id='shipimage".$i."' style='display:none'><br><br>\n\n");
}

}
else{
	errorclose("문제가 발생하였습니다.");
}
?>


<img src='./images/hackingboard.jpg'  id='boardimg' style="display:none" onload='javascript:drawingboard();'>
<img src='./images/node.png'  id='node' style="display:none">
<img src='./images/nodehover.png'  id='nodehover' style="display:none">
<img src='./images/node1.png'  id='node1' style="display:none">
<img src='./images/node2.png'  id='node2' style="display:none">
<img src='./images/node3.png'  id='node3' style="display:none">
<img src='./images/barh.png'  id='barh' style="display:none">
<img src='./images/barv.png'  id='barv' style="display:none">
<img src='./images/barv2.png'  id='barv2' style="display:none">
<script>

var board= document.getElementById('hackingboard');
var ctx=board.getContext('2d');
var boardimg=document.getElementById('boardimg');
var node=document.getElementById('node');
var nodehover=document.getElementById('nodehover');
var node1=document.getElementById('node1');
var node2=document.getElementById('node2');
var node3=document.getElementById('node3');
var barh=document.getElementById('barh');
var barv2=document.getElementById('barv2');


var revealed=new Array();

function drawingboard(){

ctx.drawImage(boardimg,0,20,boardimg.width,boardimg.height,0,0,boardimg.width,boardimg.height);
}

var nodestatus=[0,0,1,1,0,1,1,0,1,1,0,0];
var _nodehover=[0,0,0,0,0,0,0,0,0,0,0,0];
var nodex=22;
var nodey=22;

var nodes= new Array();

	nodes[0]=[88,24];
	nodes[1]=[251,24];
	nodes[2]=[128,70];
	nodes[3]=[213,70];
	nodes[4]=[3,121];
	nodes[5]=[85,121];
	nodes[6]=[256,121];
	nodes[7]=[337,121];
	nodes[8]=[128,175];
	nodes[9]=[213,175];
	nodes[10]=[87,224];
	nodes[11]=[256,224];


var lineh=new Array();
	lineh=[[154,71],[30,123],[113,123],[196,123],[281,123],[154,175]];

var linev=new Array(); linev=[[120,57],[247,57],[120,106],[161,106],[206,106],[247,106],[120,157],[161,157],[206,157],[247,157],[120,208],[247,208]];



board.onmousemove= function(ev){

	noden=mousenode(ev.offsetX,ev.offsetY)

	//document.getElementById('offsetx').innerHTML=noden;
	//document.getElementById('offsety').innerHTML=node;

	if(noden==-1){
		for(var i=0;i<12;i++){
			if(_nodehover[i]==1&& nodestatus[i]==1){
				_nodehover[i]=0;
				ctx=board.getContext('2d');
				ctx.drawImage(node,nodes[i][0]+4,nodes[i][1]-1);
				
			}
		}

	}
	else if(nodestatus[noden]==1 && _nodehover[noden]==0){
		_nodehover[noden]=1;
		ctx=board.getContext('2d');//alert();
		ctx.drawImage(nodehover,nodes[noden][0]+2,nodes[noden][1]-3);
			
	}

}

board.onclick=function(ev){

	noden=mousenode(ev.offsetX,ev.offsetY)
	if(noden==-1){
	}
	else if(nodestatus[noden]==1){
		turnonnode(noden);
	}

	else if(nodestatus[noden]==3){
		
		revealitem(noden);

	}

}

function turnonnode(n){

	if(nodestatus[n]==1 && (n==2||n==3||n==5||n==6||n==8||n==9)) {
		ctx=board.getContext('2d');

		ctx.drawImage(node2,nodes[n][0],nodes[n][1]);
		nodestatus[n]=2;


	}
	else if(nodestatus[n]==1){


		if(n==0){
			turnonbar('v',0);
		}
		else if(n==1){
			turnonbar('v',1);
		}

		else if(n==4){
			turnonbar('h',1);
		}

		else if(n==7){
			turnonbar('h',4);
		}
		else if(n==10){
			turnonbar('v',10);
		}
		else if(n==11){
			turnonbar('v',11);
		}


		ctx=board.getContext('2d');

		ctx.drawImage(node3,nodes[n][0]-3,nodes[n][1]-6);
		nodestatus[n]=3;

	}

	if(nodestatus[n]==0){
		ctx=board.getContext('2d');

		ctx.drawImage(node1,nodes[n][0],nodes[n][1]);
		nodestatus[n]=1;
	}

	if(n==2){
		turnonbar('v',3);
		if(nodestatus[5]==2)
			turnonbar('v',2);
		if(nodestatus[3]==2)
			turnonbar('h',0);
		
		turnonnode(0);
	}
	else if(n==3){
		turnonbar('v',4);
		if(nodestatus[2]==2)
			turnonbar('h',0);
		if(nodestatus[6]==2)
			turnonbar('v',5);
		turnonnode(1);
	}
	else if(n==5){
		
		if(nodestatus[2]==2)
			turnonbar('v',2);
				
		if(nodestatus[8]==2)
			turnonbar('v',6);
		
		turnonbar('h',2);

		turnonnode(4);

	}
	else if(n==6){
		if(nodestatus[3]==2)
			turnonbar('v',5);
		if(nodestatus[9]==2)
			turnonbar('v',9);
		turnonbar('h',3);
		turnonnode(7);
	}
	else if(n==8){
		turnonbar('v',7);
		if(nodestatus[5]==2)
			turnonbar('v',6);		
		if(nodestatus[9]==2)
			turnonbar('h',5);
		turnonnode(10);
	}
	else if(n==9){
		turnonbar('v',8);
		
		if(nodestatus[6]==2)
			turnonbar('v',9);		
		if(nodestatus[8]==2)
			turnonbar('h',5);
		turnonnode(11);
	}

}

function turnonbar(vh,n){

	
	if(vh=='h'){
		ctx=board.getContext('2d');
		ctx.drawImage(barh,lineh[n][0],lineh[n][1]);

	}

	else if(vh=='v'){
		ctx=board.getContext('2d');
		
		if(n==0||n==3||n==5||n==6||n==8||n==11){
			
			ctx.drawImage(barv,linev[n][0]-18,linev[n][1]-18);
		}

		else{

			ctx.drawImage(barv2,linev[n][0]-18,linev[n][1]-18);
			
		}
	}

}


function mousenode(x,y){
	


	for(var i=0;i<12;i++){
		if(x>nodes[i][0] && x<nodes[i][0]+nodex && y>nodes[i][1] && y<nodes[i][1]+nodey ){

			return i;
		}

	}
	return -1;
}


function revealitem(nn){

	if(revealed[0] && nn==0){
<?php	echo("if(confirm('".$shipdata[0][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[0]['id'].");");
	?>
	}
	else if(revealed[1] && nn==1){
<?php	echo("if(confirm('".$shipdata[1][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[1]['id'].");");
	?>
	}
	else if(revealed[2] && nn==4){
<?php	echo("if(confirm('".$shipdata[2][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[2]['id'].");");
	?>
	}
	else if(revealed[3] && nn==7){
<?php	echo("if(confirm('".$shipdata[3][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[3]['id'].");");
	?>
	}
	else if(revealed[4] && nn==10){
<?php	echo("if(confirm('".$shipdata[4][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[4]['id'].");");
	?>
	}
	else if(revealed[5] && nn==11){
<?php	echo("if(confirm('".$shipdata[5][name]."을/를 선택하시겠습니까?'))selectitem(".$shipdata[5]['id'].");");
	?>
	}
	
	
	if(nn==0){

		var revimg=document.getElementById('shipimage0');
		var tx=nodes[nn][0]+24;
		var ty=nodes[nn][1]-20;
		revealed[0]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[0]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[0]['price'])." ISK',tx,ty+85);");
	?>

	}
	else if(nn==1){
		
		var revimg=document.getElementById('shipimage1');
		var tx=nodes[nn][0]+24;
		var ty=nodes[nn][1]-20;
		revealed[1]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[1]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[1]['price'])." ISK',tx,ty+85);");
	?>

	}
	else if(nn==4){

		var revimg=document.getElementById('shipimage2');
		var tx=nodes[nn][0]+24;
		var ty=nodes[nn][1]-20;
		revealed[2]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[2]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[2]['price'])." ISK',tx,ty+85);");
	?>

	}
	else if(nn==7){

		var revimg=document.getElementById('shipimage3');
		var tx=nodes[nn][0]-70;
		var ty=nodes[nn][1]-20;
		revealed[3]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[3]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[3]['price'])." ISK',tx,ty+85);");
	?>

	}
	else if(nn==10){

		var revimg=document.getElementById('shipimage4');
		var tx=nodes[nn][0]+22;
		var ty=nodes[nn][1]-70;
		revealed[4]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[4]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[4]['price'])." ISK',tx,ty+85);");
	?>

	}
	else if(nn==11){

		var revimg=document.getElementById('shipimage5');
		var tx=nodes[nn][0]-60;
		var ty=nodes[nn][1]-90;
		revealed[5]=true;
		ctx.drawImage(revimg,0,0,128,128,tx,ty,60,60);
		ctx.fillStyle='white';
		ctx.font='13px shentox';
<?php
		echo("ctx.fillText('".$shipdata[5]['name']."',tx,ty+72);");
		echo("ctx.font='10px shentox';");
		echo("ctx.fillText('".number_format($shipdata[5]['price'])." ISK',tx,ty+85);");
	?>

	}
	

}

</script>

<script>

function selectitem( itemid){
	
	document.writeln('<form id=getitemform method="post" action="./getitem.php"><input type=hidden name=item_id value='+itemid+' ></form>');

	document.getElementById('getitemform').submit();
	

}

</script>

