# XTREAM MAIN Server

[![License](https://img.shields.io/github/license/Vateron-Media/Xtream_main)](LICENSE)
[![Release](https://img.shields.io/github/v/release/Vateron-Media/Xtream_main?label=Release&color=green)](https://github.com/Vateron-Media/Xtream_main/releases)
[![Forks](https://img.shields.io/github/forks/Vateron-Media/Xtream_main?style=flat)](https://github.com/Vateron-Media/Xtream_main/fork)
[![Stars](https://img.shields.io/github/stars/Vateron-Media/Xtream_main?style=flat)](https://github.com/Vateron-Media/Xtream_main/stargazers)
[![Issues](https://img.shields.io/github/issues/Vateron-Media/Xtream_main)](https://github.com/Vateron-Media/Xtream_main/issues)
[![Pull Requests](https://img.shields.io/github/issues-pr/Vateron-Media/Xtream_main)](https://github.com/Vateron-Media/Xtream_main/pulls)
[![All Contributors](https://img.shields.io/badge/all_contributors-2-orange.svg)](CONTRIBUTORS.md)

## 📌 About Xtream Main

Xtream Main is a powerful and scalable IPTV streaming server designed for efficient media content delivery. It supports a wide range of streaming protocols and provides an intuitive management panel.

## 🚀 Latest Release Documentation

Documentation:
[🇬🇧 English](https://github.com/Vateron-Media/Xtream_main/blob/main/doc/en/main-page.md)|[🇷🇺 Русский](https://github.com/Vateron-Media/Xtream_main/blob/main/doc/ru/main-page.md)

## ⚙️ Installation
To install Xtream Main, follow the instructions in the **[Xtream Install repository](https://github.com/Vateron-Media/Xtream_install)**.

## ⚠️ Important Note
- When uploading or managing media files through the Xtream admin panel, **do not use Cyrillic characters or special symbols** in filenames. This applies to video, audio, and image files.

## 📡 Streaming URLs
| Platform | URL Format |
|----------|------------|
| **XC_VM** | `http://<host>:25500` |
| **MAG/Stalker Portal** | `http://<host>:25461/stalker_portal/c/` |
| **M3U Playlist** | `http://<host>:25461/get.php?username=test&password=test&type=m3u_plus&output=hls&key=live` |

📌 **Refer to the API documentation for details on GET request parameters.**

## 🛠️ Managing the Panel
To start the Xtream Codes panel, use:
```sh
sudo systemctl start xc_vm
```
| Command | Description |
|---------|------------|
| `start` | Start the panel |
| `stop` | Stop the panel |
| `restart` | Restart the panel |
| `reload` | Reload Nginx configuration |

To check the panel status:
```sh
sudo systemctl status xc_vm.service
```

## 📊 Monitoring
View logs in real time:
  ```sh
  journalctl -u xc_vm -f
  ```

## 🤝 Contributing
We welcome contributions! Check out our [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to get involved.
You can also view the list of [contributors](CONTRIBUTORS.md).

## 📜 License
This project is licensed under the [AGPL-3.0 License](LICENSE).

