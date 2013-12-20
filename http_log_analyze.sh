#!/bin/bash


function get_operator () 
{
    if [ $# -ne 1 ];then
        echo "usage: get_idc_name 221.179.193.99\n" && exit 1
    else 
    { 
        if [[ $1 =~ ^221.179 ]];then
            echo "idc_name=fengtai;operator=mobile"
        elif [[ $1 =~ ^111.13 ]];then
            echo "idc_name=yonghegong;operator=mobile"
        elif [[ $1 =~ ^180.149 ]];then
            echo "idc_name=yongfeng;operator=telecom"
        elif [[ $1 =~ ^123.125 ]];then
            echo "idc_name=tucheng;operator=unicom"
        elif [[ $1 =~ ^123.126 ]];then
            echo "idc_name=tucheng;operator=unicom"
        elif [[ $1 =~ ^202.10[68] ]];then
            echo "idc_name=xidan;operator=unicom"
        elif [[ $1 =~ ^10.13 ]];then
            echo "idc_name=beixian;operator=mobile"
        else 
            echo "Error: Unknown IPADDRESS" && exit 2
        fi
    }
    fi
}

timestamp=$(date +"%s")
minute=$(date -d @${timestamp} +"%M")
stamp=$(date -d @${timestamp} +"%Y%m%d%H%M")
remainder=`expr $minute % 5`
if [ $remainder -eq 0 ];then
    five_min_ago=$(date -d "5 minutes ago" +"%H:%M")
    log_timestamp=`expr $timestamp - 300`
    log_minute=$(date -d @${log_timestamp} +"%M")
    log_time_path=$(date -d @${log_timestamp} +"%Y/%m/%d/%H")
    log_file="/data1/sinawap/logs/apache/access/3g/${log_time_path}/default.log"
    first_cha=${log_minute:0:1}
    if [[ $log_minute =~ ^[0-5]0$ ]];then
        pattern=${first_cha}[01234]        
    else 
        pattern=${first_cha}[56789]
    fi 
else
    exit 0
fi


type="log_statist"
quantity=1
ipaddr=$(/sbin/ifconfig eth0|grep "inet addr"|awk '{print $2}'|cut -d: -f2)
eval $(get_operator $ipaddr)
url="http://172.16.181.190/tongji/stat_interface.php"
time_i=1
sleep 15s

while [ ! -f $log_file ];do
        if [ $time_i -gt 55 ];then
            echo "Error: We can't find log file $log_file\n"
            exit 1
        else
            sleep 5s
            time_i=`expr $time_i + 1`
        fi
done

log_time=$(date -d @${log_timestamp} +"%Y:%H:")
regular_expression=${log_time}${pattern}:

eval $(awk -F\` 'BEGIN{
            api_count=0;
            api_traff=0;
            other_count=0;
            other_traff=0;
        }/'"$regular_expression"'/{
        if ($15 == "api.weibo.cn"){
            api_count++;
            api_traff=api_traff+$18;
        }
        else {
            other_count++;
            other_traff=other_traff+$18;
        }
        }
        END{
            print "api_count="'api_count'";api_traff="'api_traff'";other_count="'other_count'";other_traff="'other_traff'"";
        }' $log_file)


api_req=` expr $api_count / 300`
other_req=` expr $other_count / 300`
api_traff=` expr $api_traff / 300`
other_traff=` expr $other_traff / 300`
curl -d "type=$type" -d "ip=$ipaddr" -d "idc=$idc_name" -d "operator=$operator" -d  "domain=api.weibo.cn" -d "quantity=$quantity" -d "minute=$stamp" -d "request=$api_req" -d "flow=$api_traff" $url
curl -d "type=$type" -d "ip=$ipaddr" -d "idc=$idc_name" -d "operator=$operator" -d  "domain=*.weibo.cn" -d "quantity=$quantity" -d "minute=$stamp" -d "request=$other_req" -d "flow=$other_traff" $url

