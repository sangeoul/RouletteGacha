	<html>
<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'>


<?php

//image url  :		https://image.eveonline.com/Type/[typeid]_[size(64 or 32)].png
//					https://image.eveonline.com/Type/17738_64.png

//category	isShip		type		Subtype		ship
//			1/			001/		001/		001

$DIV_ITEMTYPE=1000000000;
$DIV_TYPE=1000000;
$DIV_SUBTYPE=1000;


$CATE_SHIP=1;

$CATE_CAP=1;
$CATE_BS=2;
$CATE_BC=3;
$CATE_CC=4;
$CATE_DD=5;
$CATE_FF=6;
$CATE_MN=7;
$CATE_SMALL=8;
$CATE_INDU=9;













include "phplib.php";
dbset();
logincheck();
menutable();


if(!isset($_GET['id'])) $_GET['id']=$_SESSION['roulette_user_id'];


echo ("<head><title>".getUserName($_GET['id'])."'s Collections</title>");



//필터 설정

if(!isset($_GET['category'])) {	
	$_GET['category']='all';
}

if(!isset($_GET['faction'])) {
	$_GET['faction']='all';
}
if(!isset($_GET['order'])){
	$_GET['order']='category';
}
if(!isset($_GET['owned'])){
	$_GET['owned']='all';
}
if(!isset($_GET['desc'])){
	$_GET['desc']='asc';
}

//get 으로 얻은 체크박스들을 유지한다


echo("</head>\n");


?>



<body>
<form method=GET submit='./collection.php' id='selectform'>
<table>
	<tr>
		<td class='collection_category'>
			종류
			<select name='category' id='category' style='width:80px;'>
				<option id='categoryall' value='all'>All</option>
				<option id='category6' value='6'>Frigate</option>
				<option id='category5' value='5'>Destroyer</option>
				<option id='category4' value='4'>Cruiser</option>
				<option id='category3' value='3'>Battlecruiser</option>
				<option id='category2' value='2'>Battleship</option>
				<option id='category1' value='1'>Capital</option>
			</select>
		<td class='collection_faction'>
			팩션
			<select name='faction' id='faction' style='width:60px;'>
				<option id='factionall' value='all'>All</option>
				<option id='faction1' value='1'>Amarr</option>
				<option id='faction2' value='2'>Caldari</option>
				<option id='faction3' value='3'>Gallente</option>
				<option id='faction4' value='4'>Minmatar</option>
				<option id='factionp' value='p'>Pirate</option>
			</select>
		</td>

		<td class='collection_order'>	
			정렬
			<select name='order' id='order' style='width:50px;'>
				<option id='ordercategory' value='category'>기본</option>
				<option id='orderprice' value='price'>가격순</option>
				<!--<option id='orderamount' value='amount'>보유수 순</option>-->
			</select>
		</td>
		<td class='collection_owned'>

			<select name='owned' id='owned'>
				<option id='ownedall' value='all'>All</option>
				<option id='ownedowned' value='owned'>Owned</option>
				<option id='ownedno' value='no'>Not Owned</option>
			</select>
		</td>
		<td class='collection_order'>
			<input type='checkbox' id='desc' name='desc' value='desc'>내림차순</input>
		</td>
		<td>
		<?php	echo("<input type=hidden name='id' value=".$_GET['id']."></input>");
		?>
			<input type=submit value='확인'></input>
		</td>
		<td class='collection_estimate' ><span id='estimate'></span></td>
		</tr>
		</table>
		<hr>
		
<?php
	
	//조건을 적용시켜서 걸러낸다
	$categorystring="";
	$factionstring="";

	//카테고리에 따른 분류
	if($_GET['category']!='all'){
		$categorystring=" and ((category-".($DIV_ITEMTYPE*$CATE_SHIP).") div ".$DIV_TYPE.")=".$_GET['category'];
	}

	//팩션에 따른 분류
	if($_GET['faction']=='p'){
		$factionstring=" and faction>4";
	}
	else if($_GET['faction']!='all'){
		$factionstring=" and faction=".$_GET['faction'];
	}

	//쉽 정보 불러오기 (정렬은 order 를 따른다)
	$qrall="select * from items where active=1 and (category div ".$DIV_ITEMTYPE.")=".$CATE_SHIP.$categorystring.$factionstring." order by ".$_GET['order']." ".$_GET['desc'].";";
	$resultall=$dbcon->query($qrall);





	//내가 모은 달성도 불러오기

	$qr="select id from items where active=1;";
	$result=$dbcon->query($qr);
	$allnumber=$result->num_rows;

	$point=$dbcon->query("select id from collections where active=1 and userid=".$_GET['id'].";");
	$collected=$point->num_rows;


	$estimate=0;
	echo("<table class='collections_list'>");

	echo("<tr><th class=maintitle colspan=".$COLLECTIONS_PER_LINE." height=60;> ".getUserName($_GET['id'])."의 달성도  ".$collected."/".$allnumber." (".sprintf("%.1f",($collected*100/$allnumber))."%)</th></tr>");


	//쉽 갯수만큼 다뽑는다.
	for($i=0,$j=0;$i<($resultall->num_rows);$i++) {
		
		
		 
			//줄바꿈용 <tr>
			if(($j%$COLLECTIONS_PER_LINE)==0){
				echo("<tr>\n");
			}

			//쉽정보를 받아온다
			$ship=$resultall->fetch_array();

			//내가가지고있는 정보를 받아온다.
			$qr="select * from collections where active=1 and userid=".$_GET['id']." and category=".$ship['category'];

		
			$result=$dbcon->query($qr);

			if($result->num_rows == 0){
				$collectiondata['amount']=0;
			}
			else{
				$collectiondata=$result->fetch_array();
			}

		//보유/미보유 필터를 적용시킨다.
		if($_GET['owned']=='all' || ($_GET['owned']=='owned' && $collectiondata['amount']>0 )|| ($_GET['owned']=='no' && $collectiondata['amount']==0 ) ) {

			echo("<td class='collections_list'>\n");
			echo("<table class='collection'>\n");

			echo("<tr><td class='collection_image'><img src='https://image.eveonline.com/Type/".$ship['id']."_64.png' id='img".$ship['id']."'></td></tr>");
		

			echo("<tr><td class='collection_name'>".$ship['name']."</td></tr>");
	
			echo("<tr><td class='collection_price'>".number_format($ship['price'])." ISK</td></tr>");

			echo("<tr><td class='collection_amount'>x".number_format($collectiondata['amount'])."</td></tr>");

			

			echo("</table>\n");
			if($collectiondata['amount']==0){
				echo("<script>document.getElementById('img".$ship['id']."').style.opacity='0.45';</script>");
			}
			echo("</td>\n");

			//Estimate를 더한다.
			$estimate+=$collectiondata['amount']*$ship['price'];

			//개수에 따라서 줄바꿈 해준다.
			if(($j%$COLLECTIONS_PER_LINE)==($COLLECTIONS_PER_LINE-1)){
				echo("</tr>\n");
			}
			
			$j++;
		}
			
	}

	echo("</table>");



echo("<script>\n");


echo("document.getElementById('category".$_GET['category']."').selected='selected';");
echo("document.getElementById('faction".$_GET['faction']."').selected='selected';");
echo("document.getElementById('order".$_GET['order']."').selected='selected';");
echo("document.getElementById('owned".$_GET['owned']."').selected='selected';");
echo("document.getElementById('estimate').innerHTML='Est. ".number_format($estimate)." ISK'");

if($_GET['desc']=='desc'){
	echo("document.getElementById('desc').checked=true; \n");

}

echo("</script>\n");

?>
<script>


</script>
</body>
</html>