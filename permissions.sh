sudo find /home/xtreamcodes/ -type d -exec chmod 755 {} \;
sudo find /home/xtreamcodes/ -type f -exec chmod 550 {} \;

chmod 0750 /home/xtreamcodes/bin
chmod 0750 /home/xtreamcodes/config
chmod 0750 /home/xtreamcodes/content
chmod 0750 /home/xtreamcodes/signals
# chmod 1777 /home/xtreamcodes/tmp

# chmod 0550 /home/xtreamcodes/bin/nginx
# chmod 0550 /home/xtreamcodes/bin/nginx_rtmp
# chmod 0550 /home/xtreamcodes/bin/php
chmod 0771 /home/xtreamcodes/bin/daemons.sh
chmod 0551 /home/xtreamcodes/bin/ffmpeg
chmod 0551 /home/xtreamcodes/bin/ffprobe

chmod 0660 /home/xtreamcodes/bin/php/sockets/*

chmod 0644 /home/xtreamcodes/database.sql
# chmod 0755 /home/xtreamcodes/bin/php/*.pid
chmod a+x /home/xtreamcodes/status

sudo chmod +x /home/xtreamcodes/bin/nginx_rtmp/sbin/nginx_rtmp
chown xtreamcodes:xtreamcodes -R /home/xtreamcodes > /dev/null
