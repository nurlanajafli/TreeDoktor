#!/bin/bash

#############Config Vars#############
ssh_login='root'
ssh_ip='138.197.159.118'
ssh_password=''

local_directory='/home/blacklabel/Документы/treedumps/'
server_directory='/var/www/treedoctors-crm/docs/'
#server_directory='/var/www/dev_treedoctors/test/'
#############Config Vars#############



#############functions###############
synchronization()
{
	count=0
	result=0
	while [ $count -lt 10 ] 
	do
		if ping -q -c 1 -W 1 8.8.8.8 >/dev/null; then
			#-azP
			rsync -az $ssh_login@$ssh_ip:$server_directory $local_directory	
			result=1
			break 
		fi

		sleep 10
		count=`expr $count + 1`	
	done
	echo $result
}

error_message()
{
	zenity --error --text 'td.onlineoffice.io Synchronization error!!! Сonnect to the Internet.'
}
#############functions###############




###############Sync Process##################
attempts=0
is_sync=0
while [ $attempts -lt 2 ] 
do
	echo "running synchronization with address $ssh_ip..."
	
	is_sync=`synchronization`
	

	if [ $is_sync -eq 1 ]
	then
		echo "success..... Finish"
		break
	else
		echo "IPv4 is down"
	fi

	attempts=`expr $attempts + 1`

	if [ $attempts -eq 2 ] 
	then
		error_message
	else
		sleep 60
	fi
done

###############Sync Process##################



