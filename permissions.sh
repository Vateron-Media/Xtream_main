sudo find /home/xc_vm/ -type d -exec chmod 755 {} \;
sudo find /home/xc_vm/ -type f -exec chmod 550 {} \;
sudo find /home/xc_vm/bin/ffmpeg_bin -type f -exec chmod 551 {} \;

chmod 0750 /home/xc_vm/bin
chmod 0750 /home/xc_vm/config
chmod 0750 /home/xc_vm/content
chmod 0750 /home/xc_vm/signals
chmod -R 0777 /home/xc_vm/includes

# chmod 0550 /home/xc_vm/bin/nginx
# chmod 0550 /home/xc_vm/bin/nginx_rtmp
# chmod 0550 /home/xc_vm/bin/php
chmod 0771 /home/xc_vm/bin/daemons.sh
chmod 0660 /home/xc_vm/bin/php/sockets/*
chmod 0755 /home/xc_vm/bin/redis/redis-server

# chmod 0644 /home/xc_vm/database.sql
# chmod 0755 /home/xc_vm/bin/php/*.pid
chmod a+x /home/xc_vm/status

sudo chmod +x /home/xc_vm/bin/nginx_rtmp/sbin/nginx_rtmp
chown xc_vm:xc_vm -R /home/xc_vm > /dev/null
