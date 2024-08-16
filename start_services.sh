#! /bin/bash
kill $(ps aux | grep 'xtreamcodes' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
sleep 1
kill $(ps aux | grep 'xtreamcodes' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
sleep 1
kill $(ps aux | grep 'xtreamcodes' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
sleep 4
rm -f /home/xtreamcodes/bin/php/*.pid
sudo -u xtreamcodes /home/xtreamcodes/bin/php/bin/php /home/xtreamcodes/crons/setup_cache.php
sudo -u xtreamcodes /home/xtreamcodes/bin/php/bin/php /home/xtreamcodes/tools/signals.php >/dev/null 2>/dev/null &
chown -R xtreamcodes:xtreamcodes /sys/class/net
chown -R xtreamcodes:xtreamcodes /home/xtreamcodes
sleep 4
sudo -u xtreamcodes /home/xtreamcodes/bin/nginx_rtmp/sbin/nginx_rtmp
sudo -u xtreamcodes /home/xtreamcodes/bin/nginx/sbin/nginx
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/VaiIb8.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/VaiIb8.conf
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/JdlJXm.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/JdlJXm.conf
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/CWcfSP.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/CWcfSP.conf
