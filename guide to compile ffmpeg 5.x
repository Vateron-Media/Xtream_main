cd /usr/local/src
  sudo git clone --branch release/5.1 https://github.com/FFmpeg/FFmpeg.git ffmpeg-5
  cd ffmpeg-5
  sudo ./configure --prefix=/usr/local --enable-gpl --enable-nonfree --enable-libass --enable-libfreetype --enable-libmp3lame --enable-libopus --enable-libvorbis --enable-libvpx --enable-    libx264 --enable-libx265
  sudo make -j$(nproc)
  sudo make install

<h3>After this verify the version:</h3>
  /usr/local/bin/ffmpeg -version


<h3>Copy the new files in XUI</h3>
mkdir -p /home/xui/bin/ffmpeg_bin/5.0
cp /usr/local/bin/ffmpeg /home/xui/bin/ffmpeg_bin/5.0/ffmpeg
cp /usr/local/bin/ffprobe /home/xui/bin/ffmpeg_bin/5.0/ffprobe
chown -R xui:xui /home/xui/bin/ffmpeg_bin/
chmod +x /home/xui/bin/ffmpeg_bin/5.0/ffmpeg
chmod +x /home/xui/bin/ffmpeg_bin/5.0/ffprobe
