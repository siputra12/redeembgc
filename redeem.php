<?php
$file = @explode("\n", str_replace(array("\r", "\t", " "), "", @file_get_contents(readline("GiftcardFiles [?]\t"))));
$c = count($file);
if($c<1) exit("File not found.\n");
else echo "Found $c Gifcard!\n";
$r = file_exists("csrf.bgc") ? readline("Use Existing Cookies?(y/n)") : false;
if($r == "n" or !$r){
	$csrf = @file_put_contents("csrf.bgc", readline("CsrfToken [?]\t"));
	$cook = @file_put_contents("cookies.bgc", readline("Cookies [?]\t"));
}
$jeda = readline("Sleeps [?](def 10 sec)\t") ?: $jeda;
$die_limit = 2;
$d = 0; $r = true;
foreach($file as $code){
	echo date("[H:i:s] ", time())."$code";
	Redeem:
		$rd = redeem($code);
		if(!$rd or !is_array($rd)) goto Redeem;
		if(@$rd['code'] !== "000000") $d++;
		echo " => ".@json_encode($rd['data'])."\n";
		if($d >= $die_limit) $r = strtolower(readline("[!] Gifcard die count equals Die Limit, want to continue?(y/n)")) === 'y' ? true : false;
		if(!$r) exit("\nStoppedd!!\n");
		else $d = 0;
		sleeps($jeda, "*continue in %%...");
}

function redeem($code){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.binance.com/bapi/c2c/v1/private/giftcard/redeem-card');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"cardCode\":\"$code\"}");
	$headers = array();
	$headers[] = 'Authority: www.binance.com';
	$headers[] = 'Accept: */*';
	$headers[] = 'Clienttype: web';
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Cookie: '.@file_get_contents("cookies.bgc");
	$headers[] = 'Csrftoken: '.@file_get_contents("csrf.bgc");
	$headers[] = 'Lang: en';
	$headers[] = 'Origin: https://www.binance.com';
	$headers[] = 'Referer: https://www.binance.com/en/gift-card';
	$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	curl_close($ch);
	return @json_decode($result, true);
}

function sleeps($t, $word = "", $zz = "%%"){
	$b = $t;
	Awal:
		if($b<10) $d = "0$b";
		else $d = $b;
		echo str_replace($zz, $d, $word)."\r";
		$b -= 1;
		sleep(1);
		if($b>0) goto Awal;
	return true;
}
