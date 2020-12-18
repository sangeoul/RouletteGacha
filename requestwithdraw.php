<?php
include 'phplib.php';

dbset();
logincheck();


$qr="select balance from accounts where active=1 and id=".$_SESSION['roulette_user_id'];
$result=$dbcon->query($qr);
$balance=$result->fetch_array();



?>


<table><form action="./acceptwithdraw.php" method='post'>
	<tr>
		<td> 아래와 같은 금액을 출금 신청 합니다.(<?php echo ($MIN_WITHDRAW); ?>밀 이상부터 가능)</td>
	</tr>
	<tr>
		<td> <input type=number name='amount' id='amount' onfocusout='javascript:checkvalue()'></input></td>
	</tr>
	<tr>
		<td> <input type=submit value='신청'></input></td>
	</tr>
	</form>
</table>

<script language=javascript>



function checkvalue(){
	<?php echo ("var balance=".$balance[0].";\n"); ?>
	var amount=parseInt(document.getElementById('amount').value);
	
	if( amount< ( <?php echo ($MIN_WITHDRAW); ?> *1000000) ){
		
		amount=( <?php echo ($MIN_WITHDRAW); ?> *1000000);
		
	}
	else if (amount>balance){
		amount=balance
	}
	
	document.getElementById('amount').value=amount;
}

</script>