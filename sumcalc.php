<?php

//echo((mt_rand(0,100000000)/100000000)."<br><br>\n\n");
echo("이 확률은 매일마다 지타 가격을 반영하여 바뀝니다.(캐피탈 제외)");
include "phplib.php";
dbset();

$result=$dbcon->query("select * from items where active=1 order by name asc;");


$data;
$sumof=0;
$per=array();

for($i=0;$i<$result->num_rows;$i++){
	
	$items=$result->fetch_array();
	$data[$i]['name']=$items['name'];
	$data[$i]['price']=$items['price'];

	$per[$i]=pow($data[$i]['price'],$GACHARATE);
	$sumof+=$per[$i];

}

echo ("총 ".(100*($sumof/$sumof))." %<br><br>\n");


for($i=0;$i<$result->num_rows;$i++){
	echo($data[$i]['name']. " : " ."<br>\n"); 
	echo(sprintf("%.4f %%",100*($per[$i]/$sumof))."<br><br>\n\n");

	}

?>