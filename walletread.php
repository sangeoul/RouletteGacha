

<?php

include 'phplib.php';

dbset();
logincheck();



// CORP's first transaction id : 16514719881





//꼽월렛을 먼저읽고 개인월렛을 읽는다.
//월렛 새로고침에 쿨타임을 도입한다.
$qr="SELECT timestampdiff(second, latest_loaded, UTC_TIMESTAMP) from accounts where name='__wallettimer' and balance=1;";

$result=$dbcon->query($qr);

$lasttime=$result->fetch_array();

$lasttime=$lasttime[0];

$chargevalue=0;
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
				//errorclose("ESI error");
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
	
						$corp_journal[$wi]['date']=str_replace("T"," ",$corp_journal[$wi]['date']);
						$corp_journal[$wi]['date']=str_replace("Z"," ",$corp_journal[$wi]['date']);
						$corp_journal[$wi]['amount']=abs($corp_journal[$wi]['amount']);
						//후원한도를 점검한다
						$qr="select today_charged from accounts where active=1 and id=".$corp_journal[$wi]['first_party_id'].";";
						$result=$dbcon->query($qr);
						$charged=$result->fetch_array();
						$charged=$charged[0];
						

						//후원한도를 넘겼을 경우 따로 처리한다.
						if(($charged+$corp_journal[$wi]['amount']) > $CHARGE_LIMIT){

							$overcharged=$corp_journal[$wi]['amount']+$charged-$CHARGE_LIMIT;

							//후원한도를 넘긴 금액은 출금대기로 들어간다
							$qr="update accounts set withdrawing=withdrawing+".$overcharged." where active=1 and id=".$corp_journal[$wi]['first_party_id'].";";

							if(!$dbcon->query($qr)){

								$errorc=true;
								errorclose("후원 한도에 문제가 발생했습니다. Charge DB Error 1");
							}

							//후원한도 내의 금액은 후원금액으로 남는다
							$corp_journal[$wi]['amount']=$CHARGE_LIMIT-$charged;
							
							//오늘의 후원량을 더한다.
							$qr="update accounts set today_charged=today_charged+".$corp_journal[$wi]['amount']." where active=1 and id=".$corp_journal[$wi]['first_party_id'].";";

							
							if(!$dbcon->query($qr)){

								$errorc=true;
								errorclose("후원 한도에 문제가 발생했습니다. Charge DB Error 2");
							}
							if(!$errorc){

							echo("<script>alert('한도초과한 후원량이 있습니다: ".$overcharged." ISK. \\n출금 대기 ISK로 전환됩니다. 남은 한도 : ".($CHARGE_LIMIT-$charged-$corp_journal[$wi]['amount'])." ISK');</script>");
							}

						}

						$isactive;
						//후원할 금액이 있을때만 후원한다. 그 외의 경우 제로트랜잭션으로 기입한다
						if($corp_journal[$wi]['amount']>0){
							$isactive=1;
						}
						else{$isactive=0;				}

							//유저의 소지금을 먼저 불러온다.
							$qr="select balance from accounts where id=".$corp_journal[$wi]['first_party_id']." and active=1";
							$result=$dbcon->query($qr);
							$userbalance=$result->fetch_array();
							
					

							//트랜잭션에 정보들을 기입한다.
							$qr="insert into transactions (id,userid,name,amount,afterbalance,transaction_type,transaction_date,active) values(".$corp_journal[$wi]['id'].",".$corp_journal[$wi]['first_party_id'].",'".getUserName($corp_journal[$wi]['first_party_id'])."',".($corp_journal[$wi]['amount']*$CHARGE_MULTIPLIER).",".(($corp_journal[$wi]['amount']*$CHARGE_MULTIPLIER)+$userbalance[0]).",'후원',UTC_TIMESTAMP,".$isactive.");";

							if(!($dbcon->query($qr))){
								//errorclose($qr."DB Error 1");
								$errorc=true;
							}

							//트랜잭션에 기입이 끝났으면 유저의 소지금을 올려준다.
							$qr="update accounts set balance=(balance+".($corp_journal[$wi]['amount']*$CHARGE_MULTIPLIER).") , charge=(charge+".$corp_journal[$wi]['amount'].") where id=".$corp_journal[$wi]['first_party_id']." and active=1";

							if(!($dbcon->query($qr))){
								//errorclose($qr."DB Error 2");
								$errorc=true;
							}

							$chargevalue+=($corp_journal[$wi]['amount']);



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

		//newmaxid로 갱신시켜준다.
		$qr1="update accounts set checked_trans=".$newmaxid." where balance=1 and name='__wallettimer';";
		$qr2="update accounts set latest_loaded=UTC_TIMESTAMP where balance=1 and name='__wallettimer';";
		if(!$dbcon->query($qr1)){ errorclose("문제가 발생했습니다. DB Error C1");}
		else if(!$dbcon->query($qr2)){ errorclose("문제가 발생했습니다. DB Error C2");}
		else{ if($chargevalue>0){errorclose("갱신이 완료되었습니다. ".$chargevalue." ISK 후원 감사합니다.");}else{errorclose("갱신이 완료되었습니다.");}}

	} 
}

//else{errorclose("데이터를 불러오는 데 실패하였습니다.");}

}


/*


//월렛 새로고침에 쿨타임을 도입한다.
$qr="SELECT timestampdiff(second, latest_loaded, UTC_TIMESTAMP) from accounts where active=1 and id=".$_SESSION['roulette_user_id'].";";

$result=$dbcon->query($qr);

$lasttime=$result->fetch_array();

$lasttime=$lasttime[0];



if($lasttime>=$WALLET_COOLDOWN){


	$personal_authcurl= curl_init();

	$personal_curl_body="{'grant_type':'refresh_token','refresh_token':'".$_SESSION['roulette_refresh_token']."'}";

	curl_setopt($personal_authcurl,CURLOPT_URL,"https://login.eveonline.com/oauth/token");
	curl_setopt($personal_authcurl,CURLOPT_SSL_VERIFYPEER, $SSLauth);
	curl_setopt($personal_authcurl,CURLOPT_HTTPHEADER,array($header_type,$header_auth));
	curl_setopt($personal_authcurl,CURLOPT_POSTFIELDS,$personal_curl_body);
	curl_setopt($personal_authcurl,CURLOPT_POST,1);
	curl_setopt($personal_authcurl,CURLOPT_RETURNTRANSFER,true);


	$curl_response=curl_exec($personal_authcurl);

	
	curl_close($personal_authcurl);

	$token_data=json_decode($curl_response,true);

	if(isset($token_data["access_token"])){
$errorv=false;
		
		$fullloaded=false;
	
		for($pagen=1;!$fullloaded;$pagen++){
			$personal_authcurl= curl_init();
		
			curl_setopt($personal_authcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
			curl_setopt($personal_authcurl,CURLOPT_HTTPGET,true);
			curl_setopt($personal_authcurl,CURLOPT_HTTPHEADER,array($header_type,"Authorization: Bearer ".$token_data['access_token']));
			curl_setopt($personal_authcurl,CURLOPT_URL,"https://esi.evetech.net/latest/characters/".$_SESSION['roulette_user_id']."/wallet/journal/?datasource=tranquility&page=".$pagen);
			curl_setopt($personal_authcurl,CURLOPT_RETURNTRANSFER,true);
	
			$curl_response=curl_exec($personal_authcurl);
		
			curl_close($personal_authcurl);
		
			$personal_journal=json_decode($curl_response,true);


			
			if(!isset($personal_journal[0]['id'])){
				errorclose("데이터를 불러오는 데 실패하였습니다. ESI 에러");
				$errorv=true;
			}

			//이번에 살펴본 거래번호까지를 기록해놓는다.
			$newmaxid=intval($personal_journal[0]['id']);

			//이미 살펴본 거래인지 확인한다. 예전에 기록해놓은 max id 값을 불러와서, 이보다 작아지면 더 예전 거래이다.
			$qr="select checked_trans from accounts where id=".$_SESSION['roulette_user_id']." and active=1;";
		
			$result=$dbcon->query($qr);
			$maxid=$result->fetch_row();
			$maxid=$maxid[0];
			if($maxid>$newmaxid){
				$newmaxid=$maxid;
			}
												//이미 살펴본거래일 경우 더이상 찾는 것을 중단한다.
			for($wi=0;$wi<sizeof($personal_journal) && !$fullloaded ;$wi++){
				if($personal_journal[$wi]['id'] <= $maxid){
					$fullloaded=true;
				}
				//아직 안 본 거래일 경우 꼽에다 돈을 보낸 거래만 골라낸다.
				else if(intval($personal_journal[$wi]['second_party_id'])==$CORP_CODE){
					//골라낸 거래들 중 transactions 에 이미 등록되어있는지 점검한다.
					$qr="select * from transactions where id=".$personal_journal[$wi]['id'].";";
					$result=$dbcon->query($qr);
					//transactions에도 없으면 등록을 진행한다.

					if($result->num_rows ==0){

						$personal_journal[$wi]['date']=str_replace("T"," ",$personal_journal[$wi]['date']);
						$personal_journal[$wi]['date']=str_replace("Z"," ",$personal_journal[$wi]['date']);
						$personal_journal[$wi]['amount']=abs($personal_journal[$wi]['amount']);
						//후원한도를 점검한다

						$qr="select today_charged from accounts where active=1 and id=".$_SESSION['roulette_user_id'].";";
						$result=$dbcon->query($qr);
						$charged=$result->fetch_array();
						$charged=$charged[0];
						
						//후원한도를 넘겼을 경우 따로 처리한다.
						if(($charged+$personal_journal[$wi]['amount']) > $CHARGE_LIMIT){
							$overcharged=$personal_journal[$wi]['amount']+$charged-$CHARGE_LIMIT;
							//후원한도를 넘긴 금액은 출금대기로 들어간다
							$qr="update accounts set withdrawing=withdrawing+".$overcharged." where active=1 and id=".$_SESSION['roulette_user_id'].";";

							if(!$dbcon->query($qr)){

								$errorv=true;

								errorclose("후원 한도에 문제가 발생했습니다. Charge DB Error 3");
							}


							//후원한도 내의 금액은 후원금액으로 남는다
							$personal_journal[$wi]['amount']=$CHARGE_LIMIT-$charged;
							
							//한도에 오늘의 후원량을 더한다.
							$qr="update accounts set today_charged=today_charged+".$personal_journal[$wi]['amount']." where active=1 and id=".$_SESSION['roulette_user_id'].";";
							if(!$dbcon->query($qr)){

								$errorv=true;

								errorclose("후원 한도에 문제가 발생했습니다. Charge DB Error 4");
							}

							if(!$errorv){


							echo("<script>alert('한도초과한 후원량이 있습니다: ".$overcharged." ISK. \\n출금 대기 ISK로 전환됩니다. 남은 한도 : ".($CHARGE_LIMIT-$charged-$personal_journal[$wi]['amount'])." ISK');</script>");

							}

						}
						
						//유저의 소지금을 먼저 불러온다.
						$qr="select balance from accounts where id=".$personal_journal[$wi]['first_party_id']." and active=1";
						$result=$dbcon->query($qr);
						$userbalance=$result->fetch_array();
						
						

						//후원할 금액이 없을 경우 제로 트랜잭션(inactive)로 기록한다
							$isactive;
						if($personal_journal[$wi]['amount']>0){
							$isactive=1;
						}
							else{ $isactive=0;}
					
						//트랜잭션에 정보들을 기입한다.
						$qr="insert into transactions (id,userid,name,amount,afterbalance,transaction_type,transaction_date,active) values(".$personal_journal[$wi]['id'].",".$personal_journal[$wi]['first_party_id'].",'".getUserName($personal_journal[$wi]['first_party_id'])."',".($personal_journal[$wi]['amount']*$CHARGE_MULTIPLIER).",".(($personal_journal[$wi]['amount']*$CHARGE_MULTIPLIER)+$userbalance[0]).",'후원','".$personal_journal[$wi]['date']."',".$isactive.");";
						
						if(!($dbcon->query($qr))){
							errorclose("DB Error 1");
							$errorv=true;
						}

						//트랜잭션에 기입이 끝났으면 유저의 소지금을 올려준다.
						$qr="update accounts set balance=(balance+".($personal_journal[$wi]['amount']*$CHARGE_MULTIPLIER).") , charge=(charge+".$personal_journal[$wi]['amount'].") where id=".$personal_journal[$wi]['first_party_id']." and active=1";

						if(!($dbcon->query($qr))){
							errorclose("DB Error 2");
							$errorv=true;
						}

						$chargevalue+=($personal_journal[$wi]['amount']);

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

	if(!isset($errorv)){
		errorclose("변수설정이 되지않음. DEBGU ERRORV");

	}
	else if(!$errorv){
		echo "<script> window.opener.location.reload();</script>";

		//newmaxid로 갱신시켜준다.
		$qr="update accounts set checked_trans=".$newmaxid." where active=1 and id=".$_SESSION['roulette_user_id'].";";
		$dbcon->query($qr);

		$qr="update accounts set latest_loaded=UTC_TIMESTAMP where id=".$_SESSION['roulette_user_id']." and active=1;";

		if(!($dbcon->query($qr))) {
	
			errorclose("DB Error.");
		}

		errorclose("갱신이 완료되었습니다. ".$chargevalue." ISK 후원 감사합니다.");
	}
	else if($errorv){

		errorclose("문제가 발생하였습니다.");
	}
}

else{

	errorclose("데이터를 불러오는 데 실패하였습니다.");

	}
}

*/

else{

	//$lasttime=$WALLET_COOLDOWN-$lasttime;
	$lasttime=$CORP_WALLET_COOLDOWN-$lasttime;
	$cdstring="";
	if($lasttime>=3600){
		$cdstring=$cdstring.floor($lasttime/3600)."시간 ";
	}
	if(($lasttime%3600)>=60){
		$cdstring=$cdstring.floor(($lasttime%3600)/60)."분 ";
	}
	if(($lasttime%60)>0){
		$cdstring=$cdstring.($lasttime%60)."초";
	}

	errorclose("이미 최근에 갱신되었습니다. 조금 뒤에 시도해주세요. Cooldown : ".$cdstring);
}


?>


