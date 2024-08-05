chmod -R 0777 /home/xtreamcodes
sudo find /home/xtreamcodes/admin/ -type f -exec chmod 644 {} \;
sudo find /home/xtreamcodes/admin/ -type d -exec chmod 755 {} \;
sudo find /home/xtreamcodes/wwwdir/ -type f -exec chmod 644 {} \;
sudo find /home/xtreamcodes/wwwdir/ -type d -exec chmod 755 {} \;
sudo chmod +x /home/xtreamcodes/nginx_rtmp/sbin/nginx_rtmp
