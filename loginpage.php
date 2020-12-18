<html>
	<head>

	<meta name=viewport content="width=700, initial-scale=0.5">
		<title>SSO Login</title>
		<link rel="stylesheet" type="text/css" href="./style/mainstyle.css">

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-7625490600882004",
          enable_page_level_ads: true
     });
</script>

	</head>
	<body>

<?php
include 'phplib.php';
dbset();

if($dbcon->connect_error){
	die("Connection Failed<br>".$dbcon->connect_error);
}

//else echo "Connected MariaDB Successfully.<br><br>";

echo ("<div class=login>");

echo "Welcome to<br>New Eden Roulette !<br><img src='./images/KANGW.png'><br><br>\n";


$esiurl="https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=https://".$serveraddr."/Roulette/getesi.php&client_id=".$client_id;

echo "<a href='".$esiurl."'><img src=./images/loginbutton.jpg></a><br>\n";

//echo ("현재는 가입을 받고 있지 않습니다.");

echo ("</div>");

?>
</body>
</html>

