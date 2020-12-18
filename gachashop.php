	<html>
<link rel='stylesheet' type='text/css' href='./style/mainstyle.css'>

<head><title>Shop</title>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-7625490600882004",
          enable_page_level_ads: true
     });
</script>

</head>
<?php


include "phplib.php";
dbset();
logincheck();
menutable();



?>


<body>


<table>
	<tr>
		<td rowspan=2><img src='./images/box.png' height=200></td>
		<td>쉽을 얻을 수 있는 선물상자입니다. 6개 중 하나를 선택할 수 있습니다.<br>
		<?php
			$qresult=$dbcon->query("select lastgacha from accounts where active=1 and id=".$_SESSION['roulette_user_id'].";");
			$gacha=$qresult->fetch_array();
			if($gacha[0]!='0'){
				//echo("<a href='javascript:gachagacha()'><font color=red>확인하지 않은 상자가 있습니다!</font></a>");
			}
		?>
		<br><a href="./sumcalc.php" target='_blank'>확률표 보기</a></td>
	</tr>
	<tr>
	<?php
		echo("<td><a href='javascript:gachagacha()'><font size=7>BUY NOW </font><font size=5>(".$GACHABOX_PRICE." mil K)</font></a></td>");
	?>
	</tr>
</table>

<script>
function gachagacha(){


	var popupurl = "./opengacha.php";	//탈퇴팝업창주소

	var popupOptions = "width=450, height=300, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=no, status=yes;";    //팝업창 옵션

		window.open(popupurl,"gacha",popupOptions);


}
</script>

</body>
</html>