

<?php

include 'phplib.php';

dbset();
logincheck();




//월렛 새로고침에 쿨타임을 도입한다.
$qr="SELECT timestampdiff(second, latest_loaded, UTC_TIMESTAMP) from accounts where active=1 and name='__wallettimer' and balance=1;";

$result=$dbcon->query($qr);

$lasttime=$result->fetch_array();

$lasttime=$lasttime[0];

if($lasttime>=$CORP_WALLET_COOLDOWN){


$corpauthcurl= curl_init();

curl_setopt($corpauthcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
curl_setopt($corpauthcurl,CURLOPT_SSL_VERIFYPEER, $SSLauth);
curl_setopt($corpauthcurl,CURLOPT_HTTPHEADER,array($header_type,$corp_header_auth));
curl_setopt($corpauthcurl,CURLOPT_POSTFIELDS,$corp_curl_body);
curl_setopt($corpauthcurl,CURLOPT_POST,1);
curl_setopt($corpauthcurl,CURLOPT_RETURNTRANSFER,true);


$curl_response=curl_exec($corpauthcurl);
curl_close($corpauthcurl);

$token_data=json_decode($curl_response,true);

	if(isset($token_data["access_token"])){

		$errorc=false;
		$fullloaded=false;
	
		for($pagen=1;!$fullloaded;$pagen++){
					$corpauthcurl= curl_init();
		curl_setopt($corpauthcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
		curl_setopt($corpauthcurl,CURLOPT_HTTPGET,true);
		curl_setopt($corpauthcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$token_data['access_token']));
		curl_setopt($corpauthcurl,CURLOPT_URL,"https://esi.evetech.net/latest/corporations/98593020/wallets/1/journal/?datasource=tranquility&page=1");
		curl_setopt($corpauthcurl,CURLOPT_RETURNTRANSFER,true);



		$curl_response=curl_exec($corpauthcurl);
		curl_close($corpauthcurl);
		
		$corp_journal=json_decode($curl_response,true);


			
			if(!isset($corp_journal[0]['id'])){
				
				$errorc=true;
				$fullloaded=true;
				errorclose("ESI error");
			}

			//이번에 살펴본 거래번호까지를 기록해놓는다.
			$newmaxid=intval($corp_journal[0]['id']);

			//이미 살펴본 거래인지 확인한다. 예전에 기록해놓은 max id 값을 불러와서, 이보다 작아지면 더 예전 거래이다.
			$qr="select checked_trans from accounts where name='__wallettimer' and balance=1;";
		
			$result=$dbcon->query($qr);
			$maxid=$result->fetch_row();
			$maxid=$maxid[0];
			if($maxid>$newmaxid){
				$newmaxid=$maxid;
			}

												//이미 살펴본거래일 경우 더이상 찾는 것을 중단한다. ($fullloaded)
			for($wi=0;$wi<sizeof($corp_journal) && !$fullloaded ;$wi++){

				if($corp_journal[$wi]['id'] <= $maxid){
					$fullloaded=true;
				}
				//아직 안 본 거래일 경우 꼽이 돈을 '받은' 거래만 골라낸다.
				else if(intval($corp_journal[$wi]['second_party_id'])==$CORP_CODE){

					//골라낸 거래들 중 transactions 에 이미 등록되어있는지 점검한다.
					$qr="select * from transactions where id=".$corp_journal[$wi]['id'].";";
					$result=$dbcon->query($qr);
					//transactions에도 없으면 등록을 진행한다.

					if($result->num_rows ==0){
						
						//유저의 소지금을 먼저 불러온다.
						$qr="select balance from accounts where id=".$corp_journal[$wi]['first_party_id']." and active=1";
						$result=$dbcon->query($qr);
						$userbalance=$result->fetch_array();
						$corp_journal[$wi]['amount']=abs($corp_journal[$wi]['amount']);
					

						//트랜잭션에 정보들을 기입한다. id , userid , name , amount , afterbalance, transaction_date(now)
						$qr="insert into transactions (id,userid,name,amount,afterbalance,transaction_date) values(".$corp_journal[$wi]['id'].",".$corp_journal[$wi]['first_party_id'].",'".getUserName($corp_journal[$wi]['first_party_id'])."',".$corp_journal[$wi]['amount'].",".($corp_journal[$wi]['amount']+$userbalance[0]).",UTC_TIMESTAMP);";

						if(!($dbcon->query($qr))){
							//errorclose($qr."DB Error 1");
							$errorc=true;
						}

						//트랜잭션에 기입이 끝났으면 유저의 소지금을 올려준다.
						$qr="update accounts set balance=(balance+".$corp_journal[$wi]['amount'].") where id=".$corp_journal[$wi]['first_party_id']." and active=1";

						if(!($dbcon->query($qr))){
							errorclose($qr."DB Error 2");
							$errorc=true;
						}

					}
					// 만약 이미 기입된 내역이라면 다 살펴본 것이므로 중단한다.
					else{
						$fullloaded=true;

					}
				}//여기서 거래 1건이 처리된다.
			}
		//여기까지하면 거래 내역 한 페이지를 다 본다. 혹시 부족한 경우, 2,3번째 페이지도 읽어온다.
	}
	//여기까지 하면 모든 거래를 다 확인한 것이다.


	if(!$errorc){
		echo "<script> window.opener.location.reload();</script>";

		//newmaxid로 갱신시켜준다.
		$qr="update accounts set checked_trans=".$newmaxid." where balance=1 and name='__wallettimer';";
		$dbcon->query($qr);

		$qr="update accounts set latest_loaded=UTC_TIMESTAMP where balance=1 and name='__wallettimer';";

		if(!($dbcon->query($qr))) {
	
			errorclose("DB Error.".$qr);
		}

		errorclose("갱신이 완료되었습니다. 감사합니다.");
	}
}

else{errorclose("데이터를 불러오는 데 실패하였습니다.");}
}


/*


//월렛 새로고침에 자체 쿨타임을 도입한다.
$qr="select (registered_date<date_add(UTC_TIMESTAMP, interval -".$CORP_WALLET_COOLDOWN." second)) from accounts where name='__wallettimer';";


$result=$dbcon->query($qr);
$cooldowned=$result->fetch_row();

if($cooldowned[0]==1){

//menutable();




$corpauthcurl= curl_init();

curl_setopt($corpauthcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
curl_setopt($corpauthcurl,CURLOPT_SSL_VERIFYPEER, $SSLauth);
curl_setopt($corpauthcurl,CURLOPT_HTTPHEADER,array($header_type,$corp_header_auth));
curl_setopt($corpauthcurl,CURLOPT_POSTFIELDS,$corp_curl_body);
curl_setopt($corpauthcurl,CURLOPT_POST,1);
curl_setopt($corpauthcurl,CURLOPT_RETURNTRANSFER,true);


$curl_response=curl_exec($corpauthcurl);
//var_dump($curl_response);
curl_close($corpauthcurl);

$token_data=json_decode($curl_response,true);


if(isset($token_data["access_token"])){

	$errorc=false;

	for($pagen=1&&$fullloaded=false;!$fullloaded;$pagen++){

		$corpauthcurl= curl_init();
		curl_setopt($corpauthcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
		curl_setopt($corpauthcurl,CURLOPT_HTTPGET,true);
		curl_setopt($corpauthcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$token_data['access_token']));
		curl_setopt($corpauthcurl,CURLOPT_URL,"https://esi.evetech.net/latest/corporations/98593020/wallets/1/journal/?datasource=tranquility&page=1");
		curl_setopt($corpauthcurl,CURLOPT_RETURNTRANSFER,true);



		$curl_response=curl_exec($corpauthcurl);
		curl_close($corpauthcurl);
		
		$corp_journal=json_decode($curl_response,true);


		for($wi=0;$wi<sizeof($corp_journal);$wi++){

			$qr="select * from transactions where id=".$corp_journal[$wi]['id'].";";
			$result=$dbcon->query($qr);

			//이미 등록되어있는 거래일 경우 더이상 찾는 것을 중단한다.
			if(($result->num_rows)>0){
				$fullloaded=true;
			}
			//아닐 경우 등록한다.
			else{
				//들어온 돈만 등록해야한다.
				if($corp_journal[$wi]['first_party_id']!=98593020){
					
					$qr="select balance from accounts where id=".$corp_journal[$wi]['first_party_id']." and active=1";
					$result=$dbcon->query($qr);
					$userbalance=$result->fetch_array();

					$qr="insert into transactions (id,userid,name,amount,afterbalance,transaction_date) values(".$corp_journal[$wi]['id'].",".$corp_journal[$wi]['first_party_id'].",'".getUserName($corp_journal[$wi]['first_party_id'])."',".$corp_journal[$wi]['amount'].",".($corp_journal[$wi]['amount']+$userbalance[0]).",UTC_TIMESTAMP);";
//var_dump($curl_response);
					if(!($dbcon->query($qr))){
						errorclose($qr."DB Error 1");
						$errorc=true;
					}

					$qr="update accounts set balance=(balance+".$corp_journal[$wi]['amount'].") where id=".$corp_journal[$wi]['first_party_id']." and active=1";

					if(!($dbcon->query($qr))){
						errorclose($qr."DB Error 2");
						$errorc=true;
					}

					

				}
			}
		}
	}
	if(!$errorc){

		echo "<script> window.opener.location.reload();</script>";

		errorclose("갱신이 완료되었습니다. 감사합니다.");
	}
}

else{

	errorclose("데이터를 불러오는 데 실패하였습니다.");

}
*/
?>


