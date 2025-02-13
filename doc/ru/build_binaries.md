# 🔧 **Сборка бинарных файлов для Xtream Codes**  

## 📌 **Важная информация**  
📌 **Все действия выполняются на сервере, где установлен Xtream Codes.**  

## 📖 **Содержание**  
- [Предварительная настройка](#-предварительная-настройка)  
- [Сборка NGINX](#-сборка-nginx)  
- [Сборка NGINX-RTMP](#-сборка-nginx-rtmp)  
- [Сборка PHP-FPM](#-сборка-php-fpm)  
- [Дополнительные расширения PHP](#-установка-расширений-php)  

---

## 🛠 **Предварительная настройка**  

### 1️⃣ **Установите необходимые пакеты**  
```sh
sudo apt-get update && sudo apt-get install -y \
    build-essential libpcre3 libpcre3-dev zlib1g zlib1g-dev libssl-dev \
    libgd-dev libxml2 libxml2-dev uuid-dev libcurl4-gnutls-dev libbz2-dev \
    libzip-dev libssh2-1-dev autoconf
```

### 2️⃣ **Скачайте и распакуйте OpenSSL**  
```sh
wget https://github.com/openssl/openssl/releases/download/openssl-3.4.1/openssl-3.4.1.tar.gz
tar -xzvf openssl-3.4.1.tar.gz
```

---

## 🌐 **Сборка NGINX**  

### 1️⃣ **Скачайте исходный код**  
```sh
wget https://nginx.org/download/nginx-1.26.3.tar.gz
tar -zxvf nginx-1.26.3.tar.gz
cd nginx-1.26.3
```

### 2️⃣ **Сконфигурируйте сборку**  
```sh
./configure --prefix=/home/xtreamcodes/bin/nginx \
    --http-client-body-temp-path=/home/xtreamcodes/tmp/client_temp \
    --http-proxy-temp-path=/home/xtreamcodes/tmp/proxy_temp \
    --http-fastcgi-temp-path=/home/xtreamcodes/tmp/fastcgi_temp \
    --lock-path=/home/xtreamcodes/tmp/nginx.lock \
    --http-uwsgi-temp-path=/home/xtreamcodes/tmp/uwsgi_temp \
    --http-scgi-temp-path=/home/xtreamcodes/tmp/scgi_temp \
    --conf-path=/home/xtreamcodes/bin/nginx/conf/nginx.conf \
    --error-log-path=/home/xtreamcodes/logs/error.log \
    --http-log-path=/home/xtreamcodes/logs/access.log \
    --pid-path=/home/xtreamcodes/bin/nginx/nginx.pid \
    --with-http_ssl_module \
    --with-http_realip_module \
    --with-http_addition_module \
    --with-http_sub_module \
    --with-http_dav_module \
    --with-http_gunzip_module \
    --with-http_gzip_static_module \
    --with-http_v2_module \
    --with-ld-opt='-Wl,-z,relro -Wl,--as-needed -static' \
    --with-pcre \
    --with-http_random_index_module \
    --with-http_secure_link_module \
    --with-http_stub_status_module \
    --with-http_auth_request_module \
    --with-threads \
    --with-mail --with-mail_ssl_module \
    --with-file-aio \
    --with-cpu-opt=generic \
    --with-cc-opt='-static -static-libgcc -g -O2 -Wformat -Wall' \
    --with-openssl=/root/openssl-3.4.1
```

### 3️⃣ **Соберите бинарные файлы**  
```sh
make
```

### 4️⃣ **Проверьте версию**  
```sh
/root/nginx-1.26.3/objs/nginx -V
```

### 5️⃣ **replace the file with this binary**
```
/home/xtreamcodes/bin/nginx/sbin/nginx
```

---

## 📺 **Сборка NGINX-RTMP**  

### 1️⃣ **Скачайте исходный код**  
```sh
wget https://github.com/arut/nginx-rtmp-module/archive/refs/tags/v1.2.2.tar.gz
tar -xzvf v1.2.2.tar.gz
cd nginx-1.26.3
```

### 2️⃣ **Сконфигурируйте сборку**  
```sh
./configure --prefix=/home/xtreamcodes/bin/nginx_rtmp \
    --lock-path=/home/xtreamcodes/bin/nginx_rtmp/nginx_rtmp.lock \
    --conf-path=/home/xtreamcodes/bin/nginx_rtmp/conf/nginx.conf \
    --error-log-path=/home/xtreamcodes/logs/rtmp_error.log \
    --http-log-path=/home/xtreamcodes/logs/rtmp_access.log \
    --pid-path=/home/xtreamcodes/bin/nginx_rtmp/nginx.pid \
    --add-module=/root/nginx-rtmp-module-1.2.2 \
    --with-ld-opt='-Wl,-z,relro -Wl,--as-needed -static' \
    --with-pcre \
    --without-http_rewrite_module \
    --with-file-aio \
    --with-ipv6 \
    --with-cpu-opt=generic \
    --with-cc-opt='-static -static-libgcc -g -O2 -Wformat -Wall' \
    --with-openssl=/root/openssl-3.4.1
```

### 3️⃣ **Соберите бинарные файлы**  
```sh
make
```

### 4️⃣ **Проверьте версию**  
```sh
/root/nginx-1.26.3/objs/nginx -v
```

### 5️⃣ **replace the file with this binary**
```
/home/xtreamcodes/bin/nginx_rtmp/sbin/nginx_rtmp

---

## 🐘 **Сборка PHP-FPM**  

### 1️⃣ **Установите дополнительные зависимости**  
```sh
sudo apt-get install libcurl4-gnutls-dev libbz2-dev libzip-dev -y
```

### 2️⃣ **Скачайте исходный код**  
```sh
wget https://www.php.net/distributions/php-8.4.3.tar.gz
tar -xzvf php-8.4.3.tar.gz
cd php-8.4.3
```

### 3️⃣ **Сконфигурируйте сборку**  
```sh
./configure --prefix=/home/xtreamcodes/bin/php \
    --with-fpm-user=xtreamcodes \
    --with-fpm-group=xtreamcodes \
    --enable-gd \
    --with-jpeg \
    --with-freetype \
    --enable-static \
    --disable-shared \
    --enable-opcache \
    --enable-fpm \
    --without-sqlite3 \
    --without-pdo-sqlite \
    --enable-mysqlnd \
    --with-mysqli \
    --with-curl \
    --disable-cgi \
    --with-zlib \
    --enable-sockets \
    --with-openssl \
    --enable-shmop \
    --enable-sysvsem \
    --enable-sysvshm \
    --enable-sysvmsg \
    --enable-calendar \
    --disable-rpath \
    --enable-inline-optimization \
    --enable-pcntl \
    --enable-mbregex \
    --enable-exif \
    --enable-bcmath \
    --with-mhash \
    --with-gettext \
    --with-xmlrpc \
    --with-xsl \
    --with-libxml \
    --with-pdo-mysql \
    --disable-mbregex \
    --enable-mbstring
```

### 4️⃣ **Соберите бинарные файлы**  
```sh
make
make install
```

---

## 🔌 **Установка расширений PHP**  

### 📌 **Redis**  
```sh
/home/xtreamcodes/bin/php/bin/pecl install redis
```
🔹 При установке выберите:  
```
enable igbinary serializer support? [no] : yes
enable lzf compression support? [no] : 
enable zstd compression support? [no] : 
enable msgpack serializer support? [no] :
enable lz4 compression? [no] : 
use system liblz4? [yes] : 
```

### 📌 **MaxMindDB**  
```sh
/home/xtreamcodes/bin/php/bin/pecl install maxminddb
```

### 📌 **SSH2**  
```sh
/home/xtreamcodes/bin/php/bin/pecl install ssh2
```

### 📌 **Igbinary**  
```sh
/home/xtreamcodes/bin/php/bin/pecl install igbinary
```