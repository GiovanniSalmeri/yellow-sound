# Sound 0.8.22

Embed audio tracks.

<p align="center"><img src="sound-screenshot.png?raw=true" alt="Screenshot"></p>

## How to install an extension

[Download ZIP file](https://github.com/GiovanniSalmeri/yellow-sound/archive/main.zip) and copy it into your `system/extensions` folder. [Learn more about extensions](https://github.com/annaesvensson/yellow-update).

## How to embed audio tracks

Create a `[sound]` shortcut. 

The following argument are available, all but the first argument are optional:
 
`Source` = audio source, [supported audio sources](#sources)  
`Label` = description of the audio, wrap multiple words into quotes, [supported placeholders](#placeholders); it is ignored for third-party services  

<a id="sources"></a>The following audio sources are supported:

`name` of an audio track (MP3, OPUS, OGG, FLAC, M4A, WAV) in the `media/sounds` folder  
`name` of a playlist (M3U, PLS) in the `media/sounds` folder  
`url` of an audio track (MP3, OPUS, OGG, FLAC, M4A, WAV)  
`url` of a playlist (M3U, PLS)  
`url` of an Internet radio (MP3)  
`id` and `instance` of a [Funkwhale](https://funkwhale.audio/) track or playlist or album, written respectively as `track/id@instance`, `playlist/id@instance`, `album/id@instance`  
`id` of a [Anghami](https://www.anghami.com) song or playlist, written respectively as `song/id` and `playlist/id`  
`id` of a [Idagio](https://app.idagio.com/) album  
`id` of a [Mixcloud](https://www.mixcloud.com/) track  
`id` of a [SoundCloud](https://soundcloud.com/) track  
`id` of a [Spotify](https://open.spotify.com/) track or playlist or album, written respectively as `track/id`, `playlist/id`, `album/id`  

The `id` is the last part of the link with which the track is accessed. In some services you must look for the "embed code" in order to find it.

You should know that third-party providers collect personal data and use cookies.

<a id="placeholders"></a>The following placeholders for titles are supported:

`@artist`, `@composer`, `@performer`, `@album`, `@work`, `@title`, `@subtitle`, `@disc`, `@track`, `@date`, `@genre`, `@streamname`, `@file`

The values assigned to placeholders come from metadata, which are normally present in audio files. If necessary, you can fix them with programs known as "tag editors" (sometimes included in audio players) or with an [online service](https://tagmp3.net/).

If you want an image to be shown in the custom player, place it in the same folder as the audio file or playlist, with the name of the latter (e.g. `my_song.jpg`) or `cover` (e.g. `cover.jpg`).

## How to make a playlist

Create a text file with the extension `m3u`, e.g. `my_playlist.m3u`. Write the filename of each track in a line, for example:

    song_1.mp3
    song_2.mp3
    song_3.mp3

You can also use relative paths and URLs.

## Examples

Embedding an audio track, different tracks:

    [sound my_song.mp3]
    [sound violin/ciaccona.flac]
    [sound album/2214@funkwhale.it]
    [sound track/3VM35337X7Ro1tesUHnZ95]

Embedding a playlist:

    [sound my_playlist.m3u]

Embedding an Internet radio:

    [sound https://icestreaming.rai.it/5.mp3]

Embedding an audio track, custom label:

    [sound my_song.mp3 "@title by @artist (performed in @date)"]

## Settings

The following settings can be configured in file `system/extensions/yellow-system.ini`:

`SoundLocation` (default: `/media/sounds/`) = location for audio tracks  
`SoundDefaultTitle` (default: `@artist, <b>@title</b> (@album, @date)`) = default title for tracks  
`SoundDefaultTitleRadio` (default: `<b>@radio</b>`) = default title for radios  
`SoundComposerAsArtist` (default: `0`) = treat the composer (instead of the performer) as the artist  
`SoundFileNamePattern` (default: `@track. @artist - @title"`) = pattern for scanning file names, when metadata are missing  
`SoundShowDownloadLink` (default: `1`) = show download link, 1 or 0  

## Acknowledgements

This extension uses [various sources](#sources) for the audio tracks. Thank you for the services.

## Developer

Giovanni Salmeri. [Get help](https://datenstrom.se/yellow/help/).
