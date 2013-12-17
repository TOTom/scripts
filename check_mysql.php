<?php

function help() {
        echo "example: \n\tcheck_mysql.php -h127.0.0.1 -P3306 -uuser -ppassword \n";
        echo "\n\t-h host        :hostname";
        echo "\n\t-P port        :port";
        echo "\n\t-u user        :user";
        echo "\n\t-p password    :password";
        echo "\n\t--help";
}

$longopts = array("help");
$showslave = 'show slave status';
if ($options = getopt('h:P:u:p:',$longopts)) {
        if (empty($options['h']) || empty($options['P']) || empty($options['u']) || empty($options['p'])) {
                help();
                exit(0);
        }else{
                foreach ($options as $key => $value){
                        if ($key === "help"){
                                help();
                                exit(0);
                        }else {
                                $host = $options['h'];
                                $port = $options['P'];
                                $user = $options['u'];
                                $password = $options['p'];
                        }
                }
        }
}else {
        help();
        exit(1);
}

$link = new mysqli($host, $user, $password, $port);
if ($link->connect_errno) {
        fwrite($file, "$timestamp $link->connect_errno $link->connect_error\n");
        exit(2);
}
if ($result = $link->query($showslave)){
        while ($row = $result->fetch_assoc()){
                var_dump($row);
                $result->close();
        }
}
var_dump($options);

/*
while (1){
        $timestamp = date("Y-m-d H:i:s");
        $StartTime = get_microtime();
        $link = new mysqli('172.16.10.11', 'pu_r', '57D89m8DsdfeeeeVH5Z', 'user', 10185);
        if ($link->connect_errno) {
                fwrite($file, "$timestamp $link->connect_errno $link->connect_error\n");

        }
        $ConnectTime = get_microtime();
        if ($result = $link->query($sql)){
                $QueryTime = get_microtime();
                $result->close();
        } else {
                fwrite($file, "$timestamp $link->errno $link->error\n");
        }
        $link->close();
        $ConnectSpent = round(($ConnectTime - $StartTime) * 1000, 1);
        $QuerySpent = round(($QueryTime - $ConnectTime) * 1000, 1);
        if ($ConnectSpent > 1000 || $QuerySpent > 1000) {
                fwrite($file, "$timestamp Connect time $ConnectSpent Query time $QuerySpent\n");
        }
        sleep(1);
}
fclose($file);
*/
?>
