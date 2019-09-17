[![Build Status](https://travis-ci.com/Wallpaper-Engine-Team/wallpaper-engine-translations.svg?branch=master)](https://travis-ci.com/Wallpaper-Engine-Team/wallpaper-engine-translations)

# Wallpaper Engine Translations

This repository hosts the translation files for Wallpaper Engine ( http://store.steampowered.com/app/431960 ) to make collaboration easier.

New strings will be added to [locale/core_en-us.json](https://github.com/Wallpaper-Engine-Team/wallpaper-engine-translations/blob/master/locale/core_en-us.json) and [locale/ui_en-us.json](https://github.com/Wallpaper-Engine-Team/wallpaper-engine-translations/blob/master/locale/ui_en-us.json). They then need to be translated into the other available languages, you should submit those as pull requests.

You can find missing strings by looking at at the [Missing Translations Directory](https://github.com/Wallpaper-Engine-Team/wallpaper-engine-translations/tree/master/missing_translations). The directory is auto-generated and contains a list of all missing translations for all files (if a file is not in that directory, it means it has been translated 100%). Check the [locale/core_en-us.json](https://github.com/Wallpaper-Engine-Team/wallpaper-engine-translations/blob/master/locale/core_en-us.json) and [locale/ui_en-us.json](https://github.com/Wallpaper-Engine-Team/wallpaper-engine-translations/blob/master/locale/ui_en-us.json) respectively to get an overview of the original English strings.

You may come across strings that are in curly brackets, for example: `{{author}}`. This means they will be replaced with some real content while Wallpaper Engine is running. Make sure to not translate those as the name needs to be the same, otherwise Wallpaper Engine will not recognize this.

Some variables are hard-coded, these are mainly related to the name of Steam itself, these only change for Chinese users:

```
	{{Platform}}:   "Steam"
	{{Host}}:       "Workshop"
	{{PLATFORM}}:   "STEAM"
	{{Sdk}}:        "Steamworks"
	{{Provider}}:   "Valve"
	{{PROVIDER}}:   "VALVE"
```

If you come across these strings, for example `You can download wallpapers from the {{Platform}} {{Host}}.`, the actual text is `You can download wallpapers from the Steam Workshop.`. Just translate them like that.

The only exceptions are Chinese, where strings may be translated differently. `{{Platform}} {{Host}}` will be `Steam 创意工坊` (Simplified Chinese) or `Steam 工作坊` (Traditional Chinese) as the Steam Workshop has a different name in Chinese but not in other languages.

For more information you can check out the translation thread on Steam or send an email to support@wallpaperengine.io: http://steamcommunity.com/app/431960/discussions/7/215439774876424597/

