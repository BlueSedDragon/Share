<?php
error_reporting(0);

function utime($type=''){
    list($a,$b) = explode(' ',microtime());
    if($type === true){return str_replace('0.','.',$a);}else{return $b+$a;}
}

$t1 = utime();

function f($a,$b,$c){
    $d = array();
    
    foreach($a as $tmp1){
        $d[count($d)] = $tmp1;
    };unset($tmp1);
    foreach($b as $tmp2){
        $d[count($d)] = $tmp2;
    };unset($tmp2);
    foreach($c as $tmp3){
        $d[count($d)] = $tmp3;
    };unset($tmp3);
    
    return $d;
}

header('content-type: text/plain');

$ipv6 = @$_SERVER['HTTP_CF_CONNECTING_IPV6'];
$ipv4 = @$_SERVER['HTTP_CF_CONNECTING_IP'];
if(!empty($ipv6)){
    $ip = $ipv6;
} else {
    if(!empty($ipv4)){
        $ip = $ipv4;
    }
}

unset($ipv6,$ipv4);

$mask = '';

if(strpos($ip,'.')){
    $mask = '/16';
    for($i = 0;$i < 2;$i++){
        $ip = substr($ip,0,strripos($ip,'.'));
    };unset($i);
    $ip .= '.0.0';
} else {
    if(strpos($ip,':')){
        $mask = '/64';
        for($i = 0;$i < 4;$i++){
            $ic = strripos($ip,':');
            if(!strpos($ip,':')){
                break;
            }
            if(substr($ip,$ic - 1,$ic) == ':'){
                $ip .= ':';
                $ip = str_replace('::','',$ip);
                break;
            }
            $ip = substr($ip,0,$ic);
        };unset($i,$ic);
        if(!strpos($ip,'::')){
            $ip .= '::';
        }
    }
}

$raw_ip = $ip;
$ip .= $mask;

$lis = array('SOA','NS','A','AAAA','MX');for($i = 0;$i < count($lis);$i++){$lis[$i] = strtoupper($lis[$i]);};unset($i);
$tlis = array(5,2);

$name = $_GET['name'];
$type = $_GET['type'];

if(empty($name)){
    $d = null;
    goto endz;
}
if(empty($type)){
    $type = 'A';
}

$name = strtolower($name);
$type = strtoupper($type);

for($i = 0;$i < count($lis);$i++){
    if($lis[$i] === $type){unset($lis[$i]);}
};unset($i);
$temp2 = array($type);
foreach($lis as $temp1){
    $temp2[count($temp2)] = $temp1;
};unset($temp1);
$lis = $temp2;
unset($temp2);

$lis = array($type);

$urls = array();

foreach($lis as $ii){
    $url = 'https://dns.google.com/resolve?edns_client_subnet='.$ip.'&name='.$name.'&type='.$ii;
    $urls[count($urls)] = $url;
    $rd[$ii] = json_decode(file_get_contents($url),true);
    
    //other query is off, it used time is very long.
    $d = $rd[$ii];goto endz;
    
    if(!isset($rd[$ii]['Status'])){
        if($ii == $type){
            goto endz;
        } else {
            continue;
        }
    }
    
    if(count(f($rd[$ii]['Answer'],$rd[$ii]['Authority'],$rd[$ii]['Additional'])) == 0){
        if($ii == $type){
            $d = $rd[$ii];
            goto endz;
        } else {
            continue;
        }
    } else {
        if(!isset($rd[$ii]['Answer']) && !isset($rd[$ii]['Authority']) && !isset($rd[$ii]['Additional'])){
            if($ii == $type){
                $d = $rd[$ii];
                goto endz;
            } else {
                continue;
            }
        }
    }
    
    if($ii == $type && $rd[$ii]['Answer'][0]['type'] == 5){
        $tmp0 = $rd[$type];
        unset($rd);
        $rd[$type] = $tmp0;
        unset($tmp0);
        break;
    }
    
    $tl = array();
    foreach(f($rd[$ii]['Answer'],$rd[$ii]['Authority'],$rd[$ii]['Additional']) as $tmp3){
        $tl[count($tl)] = $tmp3['type'];
    };unset($tmp3);
    $cc = 0;
    foreach($tl as $tmp4){
        if($tmp4 === 5){$cc++;}
    };unset($tmp4);
    if($cc === count($tl) && $ii != $type){
        unset($rd[$ii]);
    };unset($tl,$cc);
};unset($ii,$url);

$d = $rd[$type];
unset($rd[$type]);

for($i = 0;$i < count($lis);$i++){
    if($lis[$i] === $type){unset($lis[$i]);}
};unset($i);

foreach($rd as $tmp1){
    if(count(f($tmp1['Answer'],$tmp1['Authority'],$tmp1['Additional'])) > 0){
        foreach($tmp1['Answer'] as $tmp2){
            $d['Additional'][count($d['Additional'])] = $tmp2;
        };unset($tmp2);
    }
};unset($tmp1);

foreach(f($d['Answer'],$d['Authority'],$d['Additional']) as $tmp10){
    foreach($tlis as $tmp11){
        if($tmp10['type'] == $tmp11){
            $isc = true;
            unset($tmp11);
            break;
        }
    };unset($tmp11);
};unset($tmp10);

if($isc){
    foreach(f($d['Answer'],$d['Authority'],$d['Additional']) as $tmp5){
        foreach($lis as $tmp7){
            $url = 'https://dns.google.com/resolve?edns_client_subnet='.$ip.'&name='.$tmp5['data'].'&type='.$tmp7;
            $urls[count($urls)] = $url;
            $tmp6 = json_decode(file_get_contents($url),true);
            
            if(!isset($tmp6['Status'])){
                continue;
            }
            
            if(count(f($tmp6['Answer'],$tmp6['Authority'],$tmp6['Additional'])) == 0){
                continue;
            } else {
                if(!isset($tmp6['Answer']) && !isset($tmp6['Authority']) && !isset($tmp6['Additional'])){
                    continue;
                }
            }
            
            $tl = array();
        
            foreach(f($tmp6['Answer'],$tmp6['Authority'],$tmp6['Additional']) as $tmp3){
                $tl[count($tl)] = $tmp3['type'];
            };unset($tmp3);
        
            $cc = 0;
            foreach($tl as $tmp4){
                if($tmp4 === 5){$cc++;}
            };unset($tmp4);
            if($cc === count($tl)){
                unset($tmp6);
            };unset($tl,$cc);
        
            foreach(f($tmp6['Answer'],$tmp6['Authority'],$tmp6['Additional']) as $tmp8){
                $d['Additional'][count($d['Additional'])] = $tmp8;
            };unset($tmp8);
        };unset($tmp7,$tmp6,$url);
    };unset($tmp12,$tmp5,$tmp9);
}

$tmp15 = array();
foreach($d['Additional'] as $tmp13){
    $tmp15[count($tmp15)] = $tmp13['data'];
};unset($tmp13);
$tmp15 = array_unique($tmp15);

$tmp15l = array();
foreach(array_flip($tmp15) as $tmp17){
    $tmp15l[count($tmp15l)] = $tmp17;
};unset($tmp17);

$tmp18 = array();
foreach($tmp15l as $i){
    $tmp19 = $d['Additional'][$i];
    $tmp19['data'] = $tmp15[$i];
    $tmp18[count($tmp18)] = $tmp19;
    unset($tmp19);
};unset($i,$tmp15,$tmp15l);

$d['Additional'] = $tmp18;
unset($tmp18);

endz:
$t2 = utime();
$t = $t2 - $t1;

unset($d['edns_client_subnet']);

if(!isset($d['Status'])){$d['Status'] = null;}

$rt1 = time();
$rt2 = utime(true);

$d['Information'] = array(
    'Time' => array(
        'RealTime' => array(
            $rt1.$rt2.'',	    
            date('Y-m-d H:i:s',$rt1).$rt2.' (UTC)'
	    ),
	    'RequestTime' => array(
            $_SERVER['REQUEST_TIME_FLOAT'].'',	
            date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).str_replace($_SERVER['REQUEST_TIME'],'',$_SERVER['REQUEST_TIME_FLOAT']).' (UTC)'
	    ),
	    'UseTime' => $t.''
    ),
    'Request' => array(
        'IP' => array(
            'Address' => $raw_ip,
            'Mask' => $mask
        ),
        'Url' => array(
            'RequestUrl' => $_SERVER['REQUEST_URI'],
            'DocumentUrl' => $_SERVER['DOCUMENT_URI']
        ),
        'UserAgent' => $_SERVER['HTTP_USER_AGENT']
    ),
    'Argv' => array(
        'QueryString' => $_SERVER['QUERY_STRING'],
        '$_GET' => $_GET
    ),
    'ID' => array(
        'ConnectId' => $_SERVER['HTTP_CF_RAY'],
        'Uuid' => str_replace("\n",'',file_get_contents('/proc/sys/kernel/random/uuid'))
    ),
    'Query' => array(
        'EcsAddress' => $ip,
        'ConnectUrl' => $urls
    )
);

$AddInfo = $d['Information'];
unset(
    $AddInfo['Request'],
    $AddInfo['Argv'],
    $AddInfo['Query']['ConnectUrl']
);

$d['Additional'][count($d['Additional'])] = array(
    'name' => '__INFORMATION__.',
    'type' => 16,
    'TTL' => 0,
    'data' => str_replace('"','\'',json_encode($AddInfo))
);

endd:
if($d['Status'] === null || $d === null){header('HTTP/1.1 400');}

$dd = json_encode($d,JSON_PRETTY_PRINT);
$hdd = json_encode($d);

header('__data__: '.$hdd);
echo $dd;

exit;