<?php

function get_redis_port() {
    $all_ports = `ps -ef|grep redis-server|awk '!/grep/{print \$NF}'|awk '{print gensub(/.*_([0-9]*)\.conf$/,"\\\\1",1)}'|sort|xargs`;
    $all_ports = trim($all_ports);
    return $all_ports;
}

function get_ipaddr($ifcfg_eth1 = '/etc/sysconfig/network-scripts/ifcfg-eth1') {
    $file = @fopen($ifcfg_eth1 , 'r');
    if($file){
        while(!feof($file)){
            $content = fgets($file);
            $A = explode('=', $content);
            if($A[0] == 'IPADDR'){
                $ipaddr = $A[1];
            }
        }
    fclose($file);
    $ipaddr = trim($ipaddr);
    return $ipaddr;
    }
}

function post_data($url, $postfields){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

$ipaddr = get_ipaddr();
$ports = get_redis_port();
$array_port = explode(" ", $ports);
$array_port = array_unique($array_port);
$day_time = time() - 86400;
$url = "http://172.16.181.190/tongji/interface.php";
$info = array('ip'=>'','port'=>'','uptime_in_seconds'=>'','used_memory'=>'','mem_fragmentation_ratio'=>'','pending_aofbuf_length'=>'','changes_since_last_save'=>'','total_connections_received'=>'','total_commands_processed'=>'','evicted_keys'=>'','keyspace_misses'=>'','role'=>'','maxmemory'=>'','appendonly'=>'','rdb_persistence'=>'','slaveof'=>'','day'=>'');

foreach($array_port as $port_key => $port_value ){
    $postfields = 'type=redis';
    $redis = new redis();
    if (!$redis->connect($ipaddr, $port_value )){
        echo "Can't connect to" . $ipaddr . ":" . $port_value;
    }
    $array_info = $redis->info();
    $array_config = $redis->config("GET", "*");
    $redis->close();
    foreach($info as $info_key => $info_value){
        switch ($info_key) {
            case 'ip':
                $info[$info_key] = $ipaddr;
                break;
            case 'port':
                $info[$info_key] = $port_value;
                break;
            case 'maxmemory':
                $info[$info_key] = $array_config[$info_key];
                break;
            case 'appendonly':
                $info[$info_key] = $array_config[$info_key];
                break;
            case 'rdb_persistence':
                if ( false == empty($array_config['save'])||false == empty($array_config['cronsave'])){
                    $info[$info_key] = 'yes';
                }else{
                    $info[$info_key] = 'no';
                }
                break;
            case 'day':
                $info[$info_key] = date("Ymd", $day_time); 
                break;
            default:
                $info[$info_key] = $array_info[$info_key];
                break;
        }
    }
    
    if ($info['role'] === 'slave'){
       $info['slaveof'] = $array_info['master_host'] . ':' . $array_info['master_port']; 
    }else{
       $info['slaveof'] = 'none'; 
    }

    foreach($info as $key => $value){
        $postfields .= '&' . $key . '=' . $value;
    }

    $result = post_data($url, $postfields);

}

?>

