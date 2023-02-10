#!/bin/bash
#
# this script will install the linBMD2 system. it must be run as root user
#
# change log
# 12/10/2021 - change to install to /home/${user}/.local/share/applications/linBMD2
# 20/11/2021 - change to allow installation on openSUSE, use of zypper and correct PHP packages

# ___________________________________________________________________________
# Database_Secured - this routine is run if the linBMD2 user create fails
# ___________________________________________________________________________
Database_Secured ()
{
	OK='notok'
	while [ ${OK} == 'notok' ]
		do
			# does user know his database root password
			dialog	--title "linBMD2 Install" \
						--yesno "\nDo you know your database root password?" 0 0
			if [ $? -ne 0 ]
				then
					# user did not want to continue
					dialog	--title "linBMD2 Install" \
								--infobox "\nYou don't know your database root password\n\nThis needs a bit more work to fix.\n\nSo let's start..." 0 0
					Database_reset_root_password
				fi
			# user knows his password so ask for it
			dialog	--title "linBMD2 Install" \
						--inputbox "\nYou know your database root password or it has just been reset. Please enter it now."  0 0 2>/home/${user}/.tmp/output.txt
			if [ $? -ne 0 ]
				then
					dialog	--title "linBMD2 In/home/${user}/.stall" \
								--infobox "\nYou cancelled the install. Bye!" 0 0
					exit
			fi
			root_password=$(</home/${user}/.tmp/output.txt)
			# repeat
			dialog	--title "linBMD2 Install" \
						--inputbox "\nEnter it again"  0 0 2>/home/${user}/.tmp/output.txt
			if [ $? -ne 0 ]
				then
					dialog	--title "linBMD2 Install" \
								--infobox "\nYou cancelled the install. Bye!" 0 0
					exit
			fi
			root_password_again=$(</home/${user}/.tmp/output.txt)
			# test same
			if [ ${root_password} != ${root_password_again} ]
				then
					dialog	--title "linBMD2 Install" \
								--colors \
								--msgbox "\n\Z1The passwords you entered are not the same. Please try again."  0 0
				else
					OK='ok'
			fi
		done
}

# ___________________________________________________________________________
# Database_reset_root_password - this routine is run if the user doesn't know his mysql root password
# ___________________________________________________________________________
Database_reset_root_password ()
{
	OK='notok'
	while [ ${OK} == 'notok' ]
		do
			# user doesn't know his password so ask for new one
			dialog	--title "linBMD2 Install" \
						--inputbox "\nYou don't know your database root password. Please enter a new one now."  0 0 2>/home/${user}/.tmp/output.txt
			if [ $? -ne 0 ]
				then
					dialog	--title "linBMD2 Install" \
								--infobox "\nYou cancelled the install. Bye!" 0 0
					exit
			fi
			root_password=$(</home/${user}/.tmp/output.txt)
			# repeat
			dialog	--title "linBMD2 Install" \
						--inputbox "\nEnter it again"  0 0 2>/home/${user}/.tmp/output.txt
			if [ $? -ne 0 ]
				then
					dialog	--title "linBMD2 Install" \
								--infobox "\nYou cancelled the install. Bye!" 0 0
					exit
			fi
			root_password_again=$(</home/${user}/.tmp/output.txt)
			# test same
			if [ ${root_password} != ${root_password_again} ]
				then
					dialog	--title "linBMD2 Install" \
									--colors \
									--msgbox "\n\Z1The passwords you entered are not the same. Please try again."  0 0
				else
					OK='ok'
			fi
		done
		# OK we have a new root password, so let's try to reset it
		dialog	--title "linBMD2 Install" \
					--infobox "\nResetting your database root password - please wait..." 0 0
		sleep 3
		# stop the database service
		dialog	--title "linBMD2 Install" \
					--infobox "\nStopping ${SERVICE} - please wait..." 0 0
		systemctl stop ${SERVICE}
		sleep 3
		systemctl is-active ${SERVICE} 1>/home/${user}/.tmp/output.txt
		ACTIVE=$(</home/${user}/.tmp/output.txt)
		if [ ${ACTIVE} == "active" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\Z1Your database manager has failed to shutdown. Please use the manual install procedure described in the user manual."  0 0
				exit
		fi
		# set up directory if mysqld
		if [ ${SERVICE} == "mysql.service" ]
			then
				dialog	--title "linBMD2 Install" \
							--infobox "\nSetting up directories for ${SERVICE} - please wait..." 0 0
				if [ ! -d /var/run/mysqld ]
					then
						mkdir /var/run/mysqld
				fi
				chmod -R 0777 /var/run/mysqld
				sleep 3
		fi
		# start in safe mode
		dialog	--title "linBMD2 Install" \
				--infobox "\nStarting database service in safe mode - please wait..." 0 0
		mysqld_safe 1>/home/${user}/.tmp/output.txt 2>/home/${user}/.tmp/output.txt --skip-grant-tables &
		# check status
		mysqladmin status 1>/home/${user}/.tmp/output.txt 2>/home/${user}/.tmp/output.txt
		mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
		if [ ${mysql_error} -gt "0" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\Z1mysql_safe failed to start panic! Please use the manual install procedure described in the user manual."  0 0
				exit
		fi
		sleep 3
		# change password
		dialog	--title "linBMD2 Install" \
					--infobox "\nUsing mysql to change your root password - please wait..." 0 0
		mysql 2>/home/${user}/.tmp/output.txt -Bse "UPDATE mysql.user SET authentication_string = PASSWORD('${root_password}') WHERE User = 'root' AND Host = 'localhost';"
		mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
		if [ ${mysql_error} -gt "0" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\Z1Change root password failed panic! Around 132\n\nPlease use the manual install procedure described in the user manual."  0 0
				exit
		fi
		mysql 2>/home/${user}/.tmp/output.txt -Bse "FLUSH PRIVILEGES;"
		mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
		if [ ${mysql_error} -gt "0" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\Z1Change root password failed panic! Around 142\n\nPlease use the manual install procedure described in the user manual."  0 0
				exit
		fi
		sleep 3
		# restart mysql.service
		dialog	--title "linBMD2 Install" \
					--infobox "\nRestarting  ${SERVICE} - please wait..." 0 0
		mysqladmin --user=root --password=${root_password} shutdown 2>/home/${user}/.tmp/output.txt
		sleep 3
		systemctl start ${SERVICE} 2>/home/${user}/.tmp/output.txt
		sleep 3
		systemctl is-active ${SERVICE} 1>/home/${user}/.tmp/output.txt
		ACTIVE=$(</home/${user}/.tmp/output.txt)
		if [ ${ACTIVE} != "active" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\Z1Database root password change panic! Please use the manual install procedure described in the user manual. Contact linBMD2@mailo.com"  0 0
				exit
		fi

		# all OK
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\Z2Your database root password has been changed to ${root_password}.\n\nMake a note of this in a secure place.\nUse a password manager such as Buttercup.\n\nLet's continue linBMD2 install..."  0 0

}
# ___________________________________________________________________________
# Mainline
# ___________________________________________________________________________
# am I root?
# make sure this script is run as root
if [[ $EUID -ne 0 ]]; then
	echo ""
	printf '\E[31m'; echo "This script MUST be run as ROOT or with SU, or SUDO. ABORTING."; printf '\E[0m'
	echo ""
	exit 1
fi

# make sure machine is online
IP='www.google.com'
ping -qc1 $IP
if [ "$?" = 0 ]
then
	echo ""
	printf '\E[32m'; echo "Your machine is connected to the internet, so I can continue."; printf '\E[0m'
	echo ""
else
	echo ""
	printf '\E[31m'; echo "It appears that you are not connected to the internet. I cannot continue. Bye"; printf '\E[0m'
	echo ""
	exit 1
fi

# which package manager is available?
declare  -a list=( 'apt-get' 'dnf' 'yum' 'zypper' )
i=0
for PACMAN in "${list[@]}"
	do
		if [ "$(type -t ${PACMAN})"  == 'file' ]
			then
				break
		fi
		i=$((i + 1))
	done

# test pacman exists
if [ "$(type -t ${PACMAN})"  != 'file' ]
	then
		echo " "
		printf '\E[31m'; echo "No Package Manager found. I can only install on APT, DNF, ZYPPER or YUM based linux distributions. Bye."; printf '\E[0m'
		echo " "
		exit
fi

# create pacman install command. This is required because zypper uses a different format from other package managers
if [[ ${PACMAN} == zypper ]]
	then
		INSTALL='-q install -y ' 
	else
		INSTALL='-qy install '
fi

		
# is dialog available ?
if [ "$(type -t dialog)"  != 'file' ]
	then
		# dialog not available, so try to install it.
		${PACMAN} ${INSTALL} dialog
		# check it was installed
		if [ "$(type -t dialog)"  != 'file' ]
			then
				# dialog still not installed
				echo ""
				printf '\E[31m'; echo "The dialog command is not availble; I cannot continue. Please use manual install as described in the user manual."; printf '\E[0m'
				echo ""
				exit
		fi
fi

# create .tmp/output.txt file
if [[ ! -d ~/.tmp ]]
	then
		mkdir ~/.tmp
fi
if [[ ! -f ~/.tmp/output.txt ]]
	then
		touch ~/.tmp/output.txt
fi

# send welcome message
dialog	--title "linBMD2 Install" \
			--colors \
			--yesno "\nWelcome to the linBMD2 FreeBMD installer.\n\nDo you wish to continue?" 0 0;
if [[ $? -ne 0 ]]
	then
		# user did not want to continue
		dialog	--title "linBMD2 Install" \
					--infobox "\nBye!" 0 0
		exit
fi

# ask for user name
dialog	--title "linBMD2 Install" \
			--inputbox "\nPlease enter your user name on this machine, then press OK."  0 0 2>~/.tmp/output.txt
if [ $? -ne 0 ]
	then
		# user did not want to continue
		if [ ${temp_created} == 'Y' ]
			then
				rm -R ~/.tmp
		fi
		dialog	--title "linBMD2 Install" \
					--infobox "\nBye!" 0 0
		exit
fi

# get user name from dialog output
user=$(<~/.tmp/output.txt)

# test blank
if [ -z $user ]
	then
		dialog	--title "linBMD2 Install" \
					--colors \
					--infobox "\n\ZB\Z1I cannot continue without your user name.\n\nFind your user name and then start this script again. Bye" 0 0
		exit
fi

# test home directory exists
if [ ! -d /home/$user ]
	then
		dialog	--title "linBMD2 Install" \
					--colors \
					--infobox "\n\ZB\Z1It looks like this user name, ${user}, is incorrect.\n\nFind your user name and then start this script again. Bye" 0 0
		exit
fi

# create temp directory for dialog output in user directory
temp_created='N'
if [ ! -d /home/${user}/.tmp ]
	then
		mkdir /home/${user}/.tmp
		temp_created='Y'
fi
# and dialog output file
if [ -f /home/${user}/.tmp/output.txt ]
	then
		rm /home/${user}/.tmp/output.txt
fi
touch /home/${user}/.tmp/output.txt
chmod 0777 /home/${user}/.tmp/output.txt

# get distribution installed
# is necessary command installed?
if [ "$(type -t hostnamectl)"  != 'file' ]
	then
		${PACMAN} ${INSTALL} hostnamectl
		# check it was installed
		if [ "$(type -t hostnamectl)"  != 'file' ]
			then
				# hostnamectl still not installed
				echo ""
				printf '\E[31m'; echo "The hostnamectl command is not availble; I cannot continue. Please use manual install as described in the user manual."; printf '\E[0m'
				echo ""
				exit
		fi
fi
# get distribution - it will be in disarray[2]
hostnamectl 1>/home/${user}/.tmp/output.txt
DISTRI=$(grep 'Operating System:' "/home/${user}/.tmp/output.txt")
IFS=": " read -a disarray <<< ${DISTRI}

# install dependencies
dialog	--title "linBMD2 Install" \
			--infobox "\nInstalling dependencies - please wait..." 0 0
${PACMAN} ${INSTALL} feh wmctrl xdotool evince geany curl util-linux 1>/home/${user}/.tmp/output.txt
sleep 3

# install a LAMP
# Mageia has a LAMP meta package, so use that if on Mageia, otherwise do it the long way
if [[ ${disarray[2]} == Mageia ]]
	then
		dialog	--title "linBMD2 Install" \
					--infobox "\nInstalling task-lamp as you are using Mageia - please wait..." 0 0
		$PACMAN ${INSTALL} task-lamp 1>/home/${user}/.tmp/output.txt
		$PACMAN ${INSTALL} php-common php-cli php-curl php-gd php-json php-mbstring php-mysqlnd php-xml php-intl php-opcache php-pdo php-tokenizer 1>/home/${user}/.tmp/output.txt
		SERVICE='mysqld.service'
	else
		# install php and php modules
		dialog	--title "linBMD2 Install" \
					--infobox "\nInstalling PHP and PHP modules - please wait..." 0 0
		$PACMAN ${INSTALL} php 1>/home/${user}/.tmp/output.txt
		$PACMAN ${INSTALL} php-common php-cli php-curl php-gd php-json php-mbstring php-mysqlnd php-xml php-intl php-opcache php-pdo php-tokenizer 1>/home/${user}/.tmp/output.txt
		sleep 3
		# mysqli driver for openSUSE is provided by php-pear-MDB2_Driver_mysqli
		if [[ ${disarray[2]} == openSUSE ]]
			then
				$PACMAN ${INSTALL} php-pear-MDB2_Driver_mysqli
				$PACMAN ${INSTALL} php-cli php-curl php-gd php-json php-mbstring php-xml php-intl php-opcache php-pdo php-tokenizer 1>/home/${user}/.tmp/output.txt
		fi
		sleep 3
		
		# is PHP at a high enough version?
		php --version 1>/home/${user}/.tmp/output.txt
		read -r firstline < /home/${user}/.tmp/output.txt
		version=${firstline:4:3}
		if [ $(echo "${version}<7.3"|bc) -eq 1 ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox  "\n\ZB\Z1Your version of PHP, ${version} is too old for linBMD2.\n\nThe PHP version must be at least 7.3.\n\nUpgrade your system and then start this installer again." 0 0
				exit
		fi

		# is mariadb or mysqld already installed
		declare  -a list=( 'mysqld' 'mariadb' )
		DBMAN="none"
		i=0
		for DBMAN in "${list[@]}"
			do
				if [ "$(type -t ${DBMAN})"  == 'file' ]
					then
						break
				fi
				i=$((i + 1))
			done

		# if maraidb is installed mysqld is an alias pointing to mariadb, so is this the case?
		systemctl status mysqld.service 1>/home/${user}/.tmp/output.txt
		count=0
		count=$(grep -c "mariadb.service" /home/${user}/.tmp/output.txt)
		if [ ${count} -gt 0 ]
			then
				DBMAN="mariadb"
		fi

		# install database engine depending on type
		case ${DBMAN} in
					'none')
						# no database manager is installed so install mariadb
						dialog	--title "linBMD2 Install" \
									--infobox "\nInstalling database manager where none existed - please wait..." 0 0
						$PACMAN ${INSTALL} mariadb-server 1>/home/${user}/.tmp/output.txt
						sleep 3
						SERVICE='mariadb.service'
						;;
					'mysqld')
						# looks like mysql is installed so do not do anything
						dialog	--title "linBMD2 Install" \
									--infobox "\nYou have the mysql server installed already" 0 0
						sleep 3
						SERVICE='mysqld.service'
						;;
					'mariadb')
						# looks like mariadb is installed so refresh it
						dialog	--title "linBMD2 Install" \
									--infobox "\nUpdating existing mariadb database manager - please wait..." 0 0
						$PACMAN ${INSTALL} mariadb-server 1>/home/${user}/.tmp/output.txt
						sleep 3
						SERVICE='mariadb.service'
						;;
		esac
fi

# Make sure the db service is started and enabled
dialog	--title "linBMD2 Install" \
			--infobox "\nStarting and enabling the database manager - please wait..." 0 0
systemctl start ${SERVICE} 2>/home/${user}/.tmp/output.txt
sleep 5
systemctl enable ${SERVICE} 2>/home/${user}/.tmp/output.txt

# does the linBMD2 directory already exist?
if [ -d /home/${user}/.local/share/applications/linBMD2 ]
	then
		dialog	--title "linBMD2 Install" \
					--defaultno \
					--colors \
					--yesno  "\n\ZB\Z1It looks like you have already installed linBMD2. Do you wish to reinstall the software? \
											\n\nTHIS WILL DESTROY ANY SCANS OR TRANSCRIPTIONS THAT YOU HAVE PREVIOUSLY CREATED. \
											\n\nIT WILL ALSO REMOVE THE linBMD2 DATABASE, IF IT ALREADY EXISTS." 0 0
		# did user confirm
		if [ $? -ne 0 ]
			then
				# user did not want to continue
				dialog	--title "linBMD2 Install" \
							--infobox "\nBye!" 0 0
				exit
		fi
		# ask again
		dialog	--title "linBMD2 Install" \
					--defaultno \
					--colors \
					--yesno "\n\ZB\Z1Are you sure?\n\nYOU WILL LOOSE EVERYTHING!" 0 0
		# did user confirm
		if [ $? -ne 0 ]
			then
				# user did not want to continue
				dialog	--title "linBMD2 Install" \
							--infobox "\nBye!" 0 0
				exit
		fi
fi

# ok so user wants to remove existing installation
# remove linBMD2 directory
dialog	--title "linBMD2 Install" \
			--infobox "\nRemoving existing linBMD2 software - please wait..." 0 0
rm -R /home/${user}/.local/share/applications/linBMD2 2>/home/${user}/.tmp/output.txt
sleep 3
# back up the database just in case
dialog	--title "linBMD2 Install" \
			--infobox "\nMaking a backup of your existing database (just in case) - please wait..." 0 0
mysql_error=0
mysqldump 2>/home/${user}/.tmp/output.txt --user=linBMD2 --password=linBMD2 --databases linBMD2 --no-tablespaces > /home/${user}/linBMD2_install_backup.sql
mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
sleep 3
# what happened?
if [ ${mysql_error} -gt "0" ]
	then
		# no backup made
		dialog	--title "linBMD2 Install" \
					--infobox "\nNo backup made since the linBMD2 database did not exist." 0 0
		sleep 3
	else
		# backup OK = database exists
		dialog	--title "linBMD2 Install" \
					--infobox "\nExisting database saved to, /home/$user/linBMD2_install_backup.sql. Use mysqldump to restore it." 0 0
		sleep 3
		dialog	--title "linBMD2 Install" \
					--infobox "\nRemove existing database - please wait..." 0 0
		mysql --user=linBMD2 --password=linBMD2 -Bse "DROP DATABASE IF EXISTS linBMD2;"
		sleep 3
fi

# install linBMD2 software
dialog	--title "linBMD2 Install" \
			--infobox "\nDownloading and installing the linBMD2 software - please wait..." 0 0
# make the linBMD directory
mkdir --parents /home/${user}/.local/share/applications/linBMD2 2>/home/${user}/.tmp/output.txt
# create .tmp download folder
if [ ! -d /home/${user}/.tmp ]
	then
		mkdir /home/${user}/.tmp
fi
# it should have been tarred with this command, "tar -cavf ~/koofr_linBMD2/linBMD2_latest.tar.gz -C ~/linBMD2_latest ." The dot at the end is important
curl -s --output /home/${user}/.tmp/linBMD2.stable.tar.gz --user linBMD2@mailo.com:igpoqq2x74gv29zo "https://app.koofr.net/content/api/v2/mounts/32d9f909-3be5-49b9-b905-4f4cd62d946a/files/get/ANYTHING_BUT_NOT_EMPTY?path=linBMD2_stable.tar.gz"
if [ $? -ne "0" ]
	then
		# software
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\ZB\Z1Software install panic! linBMD2 software not found. Contact linBMD2@mailo.com." 0 0
		exit
fi
# unpack the software
tar -xf /home/${user}/.tmp/linBMD2.stable.tar.gz -C /home/${user}/.local/share/applications/linBMD2/
if [ $? -ne "0" ]
	then
		# software
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\ZB\Z1Software install panic! linBMD2 software cannot be unpacked. Contact linBMD2@mailo.com." 0 0
		exit
fi
sleep 3
# did unpack work?
if [ ! -f /home/${user}/.local/share/applications/linBMD2/README.md ]
	then
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\ZB\Z1Software install panic! linBMD2 software cannot be unpacked. Contact linBMD2@mailo.com." 0 0
		exit
fi


# create the linBMD2 database, user
dialog	--title "linBMD2 Install" \
			--infobox "\nInstalling linBMD2 initial user and database - please wait..." 0 0
mysql_error=0
mysql 2>/home/${user}/.tmp/output.txt --user=root  -Bse \
				"CREATE DATABASE IF NOT EXISTS linBMD2;
				GRANT ALL ON linBMD2.* to 'linBMD2'@'localhost' IDENTIFIED BY 'linBMD2' WITH GRANT OPTION;
				FLUSH PRIVILEGES;"
mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
sleep 3
# errors?
if [ ${mysql_error} -gt "0" ]
	then
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\ZB\Z1Cannot create the linBMD2 database.\n\nYou may have secured your database engine with a database root password.\n\nLet's try to fix this..." 0 0
		# get root password
		Database_Secured
		# try to create the user again
		dialog	--title "linBMD2 Install" \
					--infobox "\nInstalling linBMD2 initial user and database with the database root password you entered - please wait..." 0 0
		mysql_error=0
		mysql 2>/home/${user}/.tmp/output.txt --user=root  --password=${root_password} -Bse \
				"CREATE DATABASE IF NOT EXISTS linBMD2;
				GRANT ALL ON linBMD2.* to 'linBMD2'@'localhost' IDENTIFIED BY 'linBMD2' WITH GRANT OPTION;
				FLUSH PRIVILEGES;"
		mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
		sleep 3
		if [ ${mysql_error} -gt "0" ]
			then
				dialog	--title "linBMD2 Install" \
							--colors \
							--msgbox "\n\ZB\Z1Database install panic! Around 490.\n\nPlease use the manual installation procedure as described in the manual.\n\nContact linBMD2@mailo.com" 0 0
				exit
		fi
fi

#  set up the database
mysql_error=0
mysql 2>/home/${user}/.tmp/output.txt --user=linBMD2 --password=linBMD2 --database=linBMD2 < /home/${user}/.local/share/applications/linBMD2/public/Backups/linBMD2.sql
mysql_error=$(grep -c "ERROR" /home/${user}/.tmp/output.txt)
if [ ${mysql_error} -gt "0" ]
	then
		dialog	--title "linBMD2 Install" \
					--colors \
					--msgbox "\n\ZB\Z1Database install panic! Around 558.\n\nPlease use the manual installation procedure as described in the manual.\n\nContact linBMD2@mailo.com" 0 0
		exit
fi

#setup the menu entry
dialog	--title "linBMD2 Install" \
			--infobox "\nSetting up the linBMD2 menu entry - please wait..." 0 0
sed -i "s/your_linux_user/"${user}"/" /home/${user}/.local/share/applications/linBMD2/linBMD2.desktop
if [ ! -d /home/${user}/.local/share/applications/ ]
	then
		mkdir -p /home/${user}/.local/share/applications/
fi
mv /home/${user}/.local/share/applications/linBMD2/linBMD2.desktop /home/${user}/.local/share/applications

# create rundir file
if [ -f /home/${user}/.local/share/applications/linBMD2/linBMD2_rundir.txt ]
	then
		rm /home/${user}/.local/share/applications/linBMD2/linBMD2_rundir.txt
fi
touch /home/${user}/.local/share/applications/linBMD2/linBMD2_rundir.txt
echo /home/${user}/.local/share/applications/linBMD2/ > /home/${user}/.local/share/applications/linBMD2/linBMD2_rundir.txt

# change ownership of application folder and all items and run permissions
chown -R ${user} /home/${user}/.local/share/applications/linBMD2
chmod 0777 /home/${user}/.local/share/applications/linBMD2.desktop
chmod 0777 /home/${user}/.local/share/applications/linBMD2/app/Controllers/linBMD2.sh
sleep 3

# all done
dialog	--title "linBMD2 Install" \
			--infobox "\nCongratulations! linBMD2 has been installed successfully.\n\nYou can start linBMD2 from your menu in Other section.\nSearch for the menu entry with linBMD2.\n\nEnjoy linBMD2! Bye!" 0 0
exit
