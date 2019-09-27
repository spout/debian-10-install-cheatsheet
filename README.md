# debian-10-install-cheatsheet

## sudo
```bash
su -
apt install sudo
adduser spout sudo
```

## Update
```bash
sudo apt update
sudo apt upgrade
```

## byobu
```bash
sudo apt install byobu
byobu
```

## SSH
```bash
sudo nano /etc/ssh/sshd_config
Port 7022

sudo service ssh restart
```

## Firewall
https://www.digitalocean.com/community/tutorials/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server

```bash
sudo apt install ufw

sudo nano /etc/default/ufw
IPV6=no

sudo ufw disable
sudo ufw enable

sudo ufw default deny incoming
sudo ufw default allow outgoing

# ufw allow ssh
sudo ufw allow 7022/tcp
sudo ufw allow http

sudo ufw show added
sudo ufw enable
sudo ufw status
```

## fail2ban
```bash
sudo apt install fail2ban
sudo nano /etc/fail2ban/jail.conf

destemail = votremail@domain.com
action = %(action_mwl)s

# action_ => simple ban
# action_mw => ban et envoi de mail
# action_mwl => ban, envoi de mail accompagné des logs

sudo service fail2ban restart
```

## Mail
```bash
sudo apt install exim4-config
sudo dpkg-reconfigure exim4-config
```

1. internet site; mail is sent and received directly using SMTP
2. System mail name: ENTER
3. IP-addresses: ENTER
4. Other destinations: ENTER
5. Domains to relay mail for: ENTER
6. Machines to relay mail for: ENTER
7. Keep number of DNS-queries minimal: NO
8. Delivery method: mbox format
9. Split configuration into small files: NO

## DEB.SURY.ORG
https://deb.sury.org/

```bash
sudo apt-get -y install apt-transport-https lsb-release ca-certificates
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
sudo apt-get update
```

## MariaDB
https://www.geek17.com/fr/content/debian-9-stretch-installer-et-configurer-mariadb-65
https://www.digitalocean.com/community/tutorials/how-to-install-mariadb-on-debian-9

```bash
sudo apt install mariadb-server
sudo mysql_secure_installation
```

```bash
sudo mysql -u root -p
```

```sql
USE mysql;
UPDATE user SET plugin='' WHERE user='root';
FLUSH PRIVILEGES;
EXIT;
```

```bash
mysql -u root -p
```
