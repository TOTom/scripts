#!/bin/bash

if [[ $HOSTNAME = *xxx.com ]];then 
    exit 0
fi

#myname=$(/usr/bin/who am i|awk '{print $1}')
myname=$(/bin/pwd |/bin/cut -d\/ -f4)
url="http://api.node.cluster.xxx.com.cn/phalanx/"
method="create"
/etc/init.d/puppet stop
wget http://mirrors.xxx.com.cn/base.repo -O /etc/yum.repos.d/xxxxbase.repo >/dev/null 2>&1
yum install xxxxbase-cmdb-client -y > /dev/null 2>&1
yum -y install puppet.noarch ruby-rdoc > /dev/null 2>&1

#get the new hostname from node_name
source /etc/xxxxinstall.conf
eval $(/usr/bin/cmdb -a $asset_number -f node_name|awk -F" : " '/node_name/{printf("%s=%s",$1,$2)}')
eval $(/usr/bin/cmdb -a $asset_number -f int_ip|awk -F" : " '/int_ip/{printf("%s=%s",$1,$2)}')
if [ -z $node_name ];then
    echo "$asset_number" | /bin/mail -s"$asset_number don't have the nodename" $myname@staff.xxxx.com.cn
    exit 0
fi
jsonField="{\"name\":\"$node_name\",\"type\":\"A\",\"ttl\":60,\"view_id\":\"0\",\"content\":[{\"data\":\"$int_ip\",\"state\":\"up\"}]}"

#change the system file
sed -i "s/$HOSTNAME/$node_name/" /etc/sysconfig/network
if [ $? -ne 0 ];then
    echo "$asset_number" | /bin/mail -s"$asset_number change network file failed" $myname@staff.xxxx.com.cn
    exit 1
fi
/bin/hostname ${node_name}
if [ $? -ne 0 ];then
    echo "$asset_number" | /bin/mail -s"$asset_number change hostname failed" $myname@staff.xxxx.com.cn
    exit 2
fi

httpCode=$(/usr/bin/curl --connect-timeout 3 -s -o /dev/null -H "Content-Type: application/json" -d $jsonField -w "%{http_code}" "${url}${method}")
if [ $httpCode != "200" ];then
    echo "$asset_number" | /bin/mail -s"$asset_number create dns A recode error with http_code $httpCode" $myname@staff.xxxx.com.cn
    exit 3
fi

rm -rf /var/lib/puppet/ssl/*
/etc/init.d/puppet start
/usr/sbin/puppetd --test

