# 🔧 **Building Binary Files for Xtream Codes**  

## 📌 **Important Information**  
📌 **All actions should be performed on the server where Xtream Codes is installed.**  

## 📖 **Table of Contents**  
- [Preliminary Setup](#-preliminary-setup)  
- [Building NGINX](#-building-nginx)  
- [Building NGINX-RTMP](#-building-nginx-rtmp)

---

## 🛠 **Preliminary Setup**  

### 1️⃣ **Install Required Packages**  
```sh
sudo apt-get update && sudo apt-get install -y \
    build-essential libpcre3 libpcre3-dev zlib1g zlib1g-dev libssl-dev \
    libgd-dev libxml2 libxml2-dev uuid-dev libcurl4-gnutls-dev libbz2-dev \
    libzip-dev libssh2-1-dev autoconf
```

### 2️⃣ **Download and Extract OpenSSL**  
```sh
wget https://github.com/openssl/openssl/releases/download/openssl-3.4.1/openssl-3.4.1.tar.gz
tar -xzvf openssl-3.4.1.tar.gz
```

---

## 🌐 **Building NGINX**  

### 1️⃣ **Download the Source Code**  
```sh
wget https://nginx.org/download/nginx-1.26.3.tar.gz
tar -zxvf nginx-1.26.3.tar.gz
cd nginx-1.26.3
```

### 2️⃣ **Configure the Build**  
```sh
./configure --prefix=/home/xc_vm/bin/nginx \
    --http-client-body-temp-path=/home/xc_vm/tmp/client_temp \
    --http-proxy-temp-path=/home/xc_vm/tmp/proxy_temp \
    --http-fastcgi-temp-path=/home/xc_vm/tmp/fastcgi_temp \
    --lock-path=/home/xc_vm/tmp/nginx.lock \
    --http-uwsgi-temp-path=/home/xc_vm/tmp/uwsgi_temp \
    --http-scgi-temp-path=/home/xc_vm/tmp/scgi_temp \
    --conf-path=/home/xc_vm/bin/nginx/conf/nginx.conf \
    --error-log-path=/home/xc_vm/logs/error.log \
    --http-log-path=/home/xc_vm/logs/access.log \
    --pid-path=/home/xc_vm/bin/nginx/nginx.pid \
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

### 3️⃣ **Compile the Binaries**  
```sh
make
make install
```

### 4️⃣ **Check the Version**  
```sh
/home/xc_vm/bin/nginx/sbin/nginx -V
```

---

## 📺 **Building NGINX-RTMP**  

### 1️⃣ **Download the Source Code**  
```sh
wget https://github.com/arut/nginx-rtmp-module/archive/refs/tags/v1.2.2.tar.gz
tar -xzvf v1.2.2.tar.gz
cd nginx-1.26.3
```

### 2️⃣ **Configure the Build**  
```sh
./configure --prefix=/home/xc_vm/bin/nginx_rtmp \
    --lock-path=/home/xc_vm/bin/nginx_rtmp/nginx_rtmp.lock \
    --conf-path=/home/xc_vm/bin/nginx_rtmp/conf/nginx.conf \
    --error-log-path=/home/xc_vm/logs/rtmp_error.log \
    --http-log-path=/home/xc_vm/logs/rtmp_access.log \
    --pid-path=/home/xc_vm/bin/nginx_rtmp/nginx.pid \
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

### 3️⃣ **Compile the Binaries**  
```sh
make
make install
```

### 4️⃣ **Check the Version**  
```sh
/home/xc_vm/bin/nginx_rtmp/sbin/nginx_rtmp -v
```