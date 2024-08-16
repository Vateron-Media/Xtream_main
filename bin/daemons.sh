#! /bin/bash
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/1.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/1.conf
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/2.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/2.conf
/sbin/start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/bin/php/3.pid --exec /home/xtreamcodes/bin/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/bin/php/etc/3.conf
