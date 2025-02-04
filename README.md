# XTREAM MAIN Server
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

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
## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/Divarion-D"><img src="https://avatars.githubusercontent.com/u/42798043?v=4?s=100" width="100px;" alt="Danil"/><br /><sub><b>Danil</b></sub></a><br /><a href="https://github.com/Vateron-Media/Xtream_main/commits?author=Divarion-D" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!