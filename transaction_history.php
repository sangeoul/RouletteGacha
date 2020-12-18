<html>
<head>
<title>Transaction History</title>
<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">
</head>



<script>
function walletreload(){

	var popupurl = "./walletread.php";	//Wallet 리로드 주소

	var popupOptions = "width=5, height=5, toolbar=no, menubar=no, location=no, resizable=no, scrollbars=no, status=no;";    //팝업창 옵션
		window.open(popupurl,"walletreload",popupOptions);
}

function transaction(){
	var number;
	var id;
	var userid;
	var name;
	var amount;
	var transaction_date;
	var transaction_type;
	var active;

}

var transactions=new Array();

</script>

<?php

include 'phplib.php';
dbset();
logincheck();
menutable();

$qr="select * from accounts where id=".$_SESSION['roulette_user_id']." and active=1;";
$result=$dbcon->query($qr);
$userdata=$result->fetch_array();

$qr="select * from transactions where userid=".$_SESSION['roulette_user_id']." and active=1 order by number desc limit 2000";
$result=$dbcon->query($qr);

echo ("<script>\n var TRANSACTIONS_PER_PAGE=".$TRANSACTIONS_PER_PAGE.";\n var current_balance=".$userdata['balance'].";\n");

for($i=0;$i<$result->num_rows;$i++){

	$tsdata[$i]=$result->fetch_array();
	echo "transactions[".$i."]=new transaction();\n";

	echo "transactions[".$i."].number=".$tsdata[$i]['number'].";\n";
	echo "transactions[".$i."].id=".$tsdata[$i]['id'].";\n";
	echo "transactions[".$i."].userid=".$tsdata[$i]['userid'].";\n";
	echo "transactions[".$i."].name='".$tsdata[$i]['name']."';\n";
	echo "transactions[".$i."].amount=".$tsdata[$i]['amount'].";\n";
	echo "transactions[".$i."].afterbalance=".$tsdata[$i]['afterbalance'].";\n";
	echo "transactions[".$i."].transaction_date='".$tsdata[$i]['transaction_date']."';\n";
	echo "transactions[".$i."].transaction_type='".$tsdata[$i]['transaction_type']."';\n";
	echo "transactions[".$i."].active=".$tsdata[$i]['active'].";\n";
}

echo ("</script>");

?>

<script>
document.writeln("<table class='listtable'><tr><th class='maintitle'> Transaction History : "+transactions.length+" <a href='javascript:walletreload()' style='margin-left:20px'>[Refresh]</a></th><td class='balancesum'> Current Balance<br>"+Number(current_balance).toLocaleString('en')+" K</td></tr>");

document.writeln("<tr><td align=center colspan=2><span id='index_transaction' class='indexnumbers'></tr></td>");	//상단 페이지 인덱스 배치
document.writeln("<tr><td colspan=2 align=center><table><tr><td class='transaction_list_amount'>금액</td><td class='transaction_list_type'>유형</td><td class='transaction_list_balance'> 잔고 </td><td class='transaction_list_date'>시각</td></tr></table></td></tr>")//상단 컬럼 배치
document.writeln("<tr><td align=center colspan=2><span id='transactions_list'></tr></td>");	//리스트 배치
document.writeln("</table><hr style='margin-top:120px'>");	
changeindex(1);



//인덱스 만드는것이 복잡하므로 따로 함수로 떼어낸다.
function make_index_string(currentindex,maxindex){

	var startindex;
	
	//인덱스 시작번호를 결정한다.
	if(currentindex<6) startindex=1;
	else if(currentindex>maxindex-5) startindex=maxindex-10;
	else if(currentindex>=6) startindex=currentindex-5;
	
	var indexstring="<table><tr><td class='listindex' align=center>";
	//인덱스는 11개씩 출력한다
	
	for(var i=startindex; i<startindex+11 && i<=maxindex ;i++ ){
		if(i!=currentindex)
			indexstring=indexstring+"<a class='listindex' id='tindex"+i+"' href='javascript:changeindex("+i+")'>"+i+"</a>";
		else{
			indexstring=indexstring+"<a class='listindex_selected' id='tindex"+i+"'>"+i+"</a>";
		}
	}
	

	indexstring=indexstring+"</td></tr></table>";

	document.getElementById('index_transaction').innerHTML=indexstring;
	
	
}

//인덱스를 바꾸는 것도 함수로 처리한다.
function changeindex(selectedindex){
	
	//테이블 전체를 스트링으로 처리
	var liststring="";
	

		var startn1=(selectedindex-1)*TRANSACTIONS_PER_PAGE;
		
		
		for( var i=startn1; i<(startn1 + TRANSACTIONS_PER_PAGE) && i<transactions.length; i++){
			
			if( transactions[i].transaction_type=='후원'){
				var typestring="<font color='#00d990'>후원</font>";
			}
			else if( transactions[i].transaction_type=='출석'){
				var typestring="<font color='#00B460'>출석</font>";
			}
			
			else if (transactions[i].transaction_type=='출금')
			{
				typestring="<font color='#b5953f'>출금</font>"
			}
			else if (transactions[i].transaction_type=='당첨')
			{
				typestring="<font color='#00B460'>당첨</font>"
			}
			else if (transactions[i].transaction_type=='구매')
			{
				typestring="<font color='#d5d58f'>구매</font>"
			}
			else{
				typestring=transactions[i].transaction_type;
			}

			liststring=liststring+"<table border=0><tr class='transaction_list'><td class='transaction_list_amount'>"+Number(transactions[i].amount).toLocaleString('en')+"</td><td class='transaction_list_type'>"+typestring+"</td><td class='transaction_list_balance'>"+Number(transactions[i].afterbalance).toLocaleString('en')+"</td><td class='transaction_list_date'>"+transactions[i].transaction_date+"</td></tr></table>";
		}
		document.getElementById('transactions_list').innerHTML=liststring;
		make_index_string(selectedindex,Math.ceil(transactions.length/TRANSACTIONS_PER_PAGE),"active");
		
}

</script>