# XTREAM MAIN Server

[![License](https://img.shields.io/github/license/Vateron-Media/Xtream_main)](LICENSE)
[![Forks](https://img.shields.io/github/forks/Vateron-Media/Xtream_main?style=flat)](https://github.com/Vateron-Media/Xtream_main/fork)
[![Stars](https://img.shields.io/github/stars/Vateron-Media/Xtream_main?style=flat)](https://github.com/Vateron-Media/Xtream_main/stargazers)
[![Issues](https://img.shields.io/github/issues/Vateron-Media/Xtream_main)](https://github.com/Vateron-Media/Xtream_main/issues)
[![Pull-requests](https://img.shields.io/github/issues-pr/Vateron-Media/Xtream_main)](https://github.com/Vateron-Media/Xtream_main/pulls)
[![License](https://img.shields.io/github/v/release/Vateron-Media/Xtream_main?label=Release%20Main&color=green)](https://github.com/Vateron-Media/Xtream_main/releases)
[![All Contributors](https://img.shields.io/badge/all_contributors-2-orange.svg)](CONTRIBUTORS.md)

## Latest release
- [Documentation](https://github.com/Vateron-Media/Xtream_main/blob/main/doc/en/main-page.md) | [Документация](https://github.com/Vateron-Media/Xtream_main/blob/main/doc/ru/main-page.md)


# Note

* When uploading or managing media files through the Xtream admin panel, you cannot use Cyrillic characters or special characters in the filenames. This applies to all video, audio, and image files that you plan to manage through the admin interface.

# Stream URL
#### **XC_VM** `http://<host>:25500`
#### **MAG or Stalker Portal** `http://<host>:25461/stalker_portal/c/`
#### **File IPTV list** `http://<host>:25461/get.php?username=test&password=test&type=m3u_plus&output=hls&key=live`
* See the API documentation for an explanation of parameters in a GET request

# Run panel

```
sudo systemctl start xtreamcodes
```
| Parameter |description |
| :---:   | :---: |
| start | Start panel |
| stop | Stop panel |
| restart | Restart panel |
| reload | Restart Nginx |

Panel Status
```
sudo systemctl status xtreamcodes.service 
```