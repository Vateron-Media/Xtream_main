#! /bin/bash

if pgrep -u xc_vm php-fpm8.4 > /dev/null; then
    echo "PHP-FPM is already running, stopping existing instances..."
    pkill -u xc_vm php-fpm8.4
    sleep 2
fi

# Now start PHP-FPM instances
start-stop-daemon --start --quiet --pidfile /home/xc_vm/bin/php_sockets/1.pid --exec /usr/sbin/php-fpm8.4 -- --daemonize --fpm-config /etc/php/8.4/fpm/pool.d/1.conf
start-stop-daemon --start --quiet --pidfile /home/xc_vm/bin/php_sockets/2.pid --exec /usr/sbin/php-fpm8.4 -- --daemonize --fpm-config /etc/php/8.4/fpm/pool.d/2.conf
start-stop-daemon --start --quiet --pidfile /home/xc_vm/bin/php_sockets/3.pid --exec /usr/sbin/php-fpm8.4 -- --daemonize --fpm-config /etc/php/8.4/fpm/pool.d/3.conf
start-stop-daemon --start --quiet --pidfile /home/xc_vm/bin/php_sockets/4.pid --exec /usr/sbin/php-fpm8.4 -- --daemonize --fpm-config /etc/php/8.4/fpm/pool.d/4.conf
