<form submit='./jsonitem.php' method=get>

<input id=search type=text name=search>

<input id=category type=text name=category>

<input id=faction type=text name=faction value=1>

<input type=submit value=Search>

</form>

검색어 ---------------- 카테고리넘버 ----------- 팩션<br>
--------------- --------- --- // ----------아마르1 칼다리2 갈란테3 민마타4 <br>
----------------------------------------블러드5 산샤6 구리스타7 서펜티스8 엔젤9<br> 
----------------------------------------ORE10 시스터11 모르두12 트리글라비안13 콩코드14<br><Br>

프리깃순서 : 어썰트 - 코버트 - 스바머 - 일렉어택 - 인셉 - 로지<br>


<?php


ㅁㅀㅁ조ㅓㅗㄱㄷㅁ

include "phplib.php";
dbset();
$categorynumber=100600700     ;

$arr="";
if(isset($_GET['search'])){

	$searchstring=str_replace(" ","%20",$_GET['search']);

$file = fopen("https://esi.evetech.net/latest/search/?categories=inventory_type&datasource=tranquility&language=en-us&search=".$searchstring."&strict=true","r");

	while(!feof($file)) {
	 $json .= fread($file,1024);
	}

	$arr = json_decode($json,true);

}
echo($arr['inventory_type'][0]."<br><br>");

//$qr="insert into items (id,category,name,faction) values (".$arr['inventory_type'][0].",".$categorynumber.$_GET['category'].",'".$_GET['search']."',".$_GET['faction'].");";

echo($qr."\n<br>\n");

//if($dbcon->query($qr)){
//echo("OK\n");
//}
//else("Error\n");


echo("<script>");

echo("var getsearch='".$_GET['search']."';");
echo("var getcategory='".($_GET['category']+1)."';");
echo("var getfaction='".$_GET['faction']."';");

echo("</script>");

?>
<script>

document.getElementById('search').value=getsearch;
document.getElementById('category').value=getcategory;
document.getElementById('faction').value=getfaction;

</script>