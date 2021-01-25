[![Crowdin](https://badges.crowdin.net/wallpaper-engine/localized.svg)](https://crowdin.com/project/wallpaper-engine)

# Wallpaper Engine Translations

This repository hosts the translation files for Wallpaper Engine ( http://store.steampowered.com/app/431960 ) to make collaboration easier.

If you want to contribute translations or just want to fix minor issues, you do so on Crowdin which is the translation platform that we use:

https://crwd.in/wallpaper-engine

If you have any questions about Crowdin, feel free to send us an email at support@wallpaperengine.io. Pull requests have been retired in favor of Crowdin.

---------------------------

You may come across strings that are in curly brackets, for example: `{{author}}`. This means they will be replaced with some real content while Wallpaper Engine is running. Make sure to not translate those as the name needs to be the same, otherwise Wallpaper Engine will not recognize this.

Some variables are hard-coded and use three square brackets, these are mainly related to the name of Steam itself, these only change for Chinese users:

```
	[[[Platform]]]:   "Steam"
	[[[Sdk]]]:        "Steamworks"
```

If you come across these strings, for example `You can download wallpapers from the [[[Platform]]] [[[Host]]].`, the actual text is `You can download wallpapers from the Steam Workshop.`. Just translate them like that.

The only exceptions are Chinese, where strings may be translated differently. `[[[Platform]]] [[[Host]]]` will be `Steam 创意工坊` (Simplified Chinese) or `Steam 工作坊` (Traditional Chinese) as the Steam Workshop has a different name in Chinese but not in other languages.

For more information you can check out the translation thread on Steam or send an email to support@wallpaperengine.io: http://steamcommunity.com/app/431960/discussions/7/215439774876424597/

