<?php
/*class runtime
{
    var $StartTime = 0;
    var $StopTime = 0;
    function get_microtime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
    function start()
    {
        $this->StartTime = $this->get_microtime();
    }
    function stop()
    {
        $this->StopTime = $this->get_microtime();
    }
    function spent()
    {
        return round(($this->StopTime - $this->StartTime) * 1000, 1);
    }
}*/

function get_microtime()
{
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
}

$sql = 'SELECT SQL_NO_CACHE `sina_id`, `focus`, `s_cate_id` as cate_id, `s_cate_name`, `title` as book_name, `ISBN`, `ISBN13`, `publisher`, `publish_date`, `src`, `s_bid`, `intro`, `check_status`, `pub_price`, `status` FROM (`read_books`) WHERE `sina_id` = 27871 LIMIT 1';
$log = "./check_mysql.log";
$file = fopen($log, "a");
while (1){
        $timestamp = date("Y-m-d H:i:s");
        $StartTime = get_microtime();
        $link = new mysqli('172.16.6.214', 'publish_r', '57D89m8DVH5Z', 'publish', 10185);
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
?>
