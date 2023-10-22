<?php
// Sound extension, https://github.com/GiovanniSalmeri/yellow-sound

class YellowSound {
    const VERSION = "0.8.22";
    public $yellow;         // access to API

    var $soundFieldList = [ "artist", "composer", "performer", "album", "work", "title", "subtitle", "disc", "track", "date", "genre", "radio", "file" ];
    var $separator = ", ";

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("soundLocation", "/media/sounds/");
        $this->yellow->system->setDefault("soundComposerAsArtist", 0);
        $this->yellow->system->setDefault("soundFileNamePattern", "@track. @artist - @title");
        $this->yellow->system->setDefault("soundDefaultTitle", "@artist, <b>@title</b> (@album, @date)");
        $this->yellow->system->setDefault("soundDefaultTitleRadio", "<b>@radio</b>");
        $this->yellow->system->setDefault("soundShowDownloadLink", 1);
        $this->yellow->language->setDefaults([
            "Language: en",
            "soundAudioPlayer: Audio player",
            "soundPlay: Play",
            "soundTimeline: Timeline",
            "soundTotalTime: Total time",
            "soundMute: Mute",
            "soundVolume: Volume",
            "soundPlaylist: Playlist",
            "soundDownload: Download",
            "soundLiveStreaming: Live broadcasting",
            "Language: it",
            "soundAudioPlayer: Riproduttore audio",
            "soundPlay: Riproduci",
            "soundTimeline: Barra del tempo",
            "soundTotalTime: Tempo totale",
            "soundMute: Disattiva l'audio",
            "soundVolume: Volume",
            "soundPlaylist: Elenco di riproduzione",
            "soundDownload: Preleva",
            "soundLiveStreaming: Trasmissione in diretta",
            "Language: fr",
            "soundAudioPlayer: Lecteur audio",
            "soundPlay: Lire",
            "soundTimeline: Ligne du temps",
            "soundTotalTime: Temps total",
            "soundMute: Désactiver le son",
            "soundVolume: Volume",
            "soundPlaylist: Liste de lecture",
            "soundDownload: Télécharger",
            "soundLiveStreaming: Diffusion en direct",
            "Language: de",
            "soundAudioPlayer: Audiospieler",
            "soundPlay: Wiedergeben",
            "soundTimeline: Zeitachse",
            "soundTotalTime: Gesamtzeit",
            "soundMute: Stumm schalten",
            "soundVolume: Lautstärke",
            "soundPlaylist: Wiedergabeliste",
            "soundDownload: Herunterladen",
            "soundLiveStreaming: Direktübertragung",
            "Language: es",
            "soundAudioPlayer: Reproductor de audio",
            "soundPlay: Reproducir",
            "soundTimeline: Escala de tiempo",
            "soundTotalTime: Tiempo total",
            "soundMute: Desactivar audio",
            "soundVolume: Volumen",
            "soundPlaylist: Lista de reproducción",
            "soundDownload: Descargar",
            "soundLiveStreaming: Transmisión en directo",
            "Language: pt",
            "soundAudioPlayer: Leitor de áudio",
            "soundPlay: Reproduzir",
            "soundTimeline: Linha do tempo",
            "soundTotalTime: Tempo total",
            "soundMute: Ativar Mudo",
            "soundVolume: Volume",
            "soundPlaylist: Lista de reprodução",
            "soundDownload: Baixar",
            "soundLiveStreaming: Transmissão ao vivo",
            "Language: nl",
            "soundAudioPlayer: Audiospeler",
            "soundPlay: Afspelen",
            "soundTimeline: Tijdlijn",
            "soundTotalTime: Totaal tijd",
            "soundMute: Dempen",
            "soundVolume: Volume",
            "soundPlaylist: Afspeellijst",
            "soundDownload: Downloaden",
            "soundLiveStreaming: Rechtstreekse uitzending",
        ]);
    }

    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="sound" && ($type=="block" || $type=="inline")) {
            list($id, $label) = $this->yellow->toolbox->getTextArguments($text);
            $services = [
                "item"=> [ "/^.+\.(?:mp3|opus|ogg|flac|m4a|wav)$/i", "@path@0", "custom", null ],
                "list"=> [ "/^.+\.(?:m3u|pls)$/i", "@path@0", "custom", null ],
                "url" => [ "/^\w+:.+/", "@0", "custom" ],
                "funkwhale" => [ "/^(track|playlist|album)=([0-9]+)@([A-Za-z0-9\-_]+(?:\.[A-Za-z0-9\-_]+)*)$/", "https://@3/front/embed.html?type=@1&id=@2", "iframe" ],
                "spotify" => [ "/^(track|playlist|album)=([a-zA-Z0-9]{22})$/", "https://open.spotify.com/embed/@1/@2", "iframe" ],
                "anghami" => [ "/^(song|playlist)=(\d+)$/", "https://widget.anghami.com/@1/@2/?theme=fulllight&layout=wide&lang=@lang", "iframe" ],
                "mixcloud" => [ "/^()(\/[^\/]+\/[^\/]+\/)$/", "https://www.mixcloud.com/widget/iframe/?hide_cover=1&light=1&feed=@2", "iframe" ],
                "soundcloud" => [ "/^()([A-Za-z0-9\-]+\/[A-Za-z0-9\-]+)$/", "https://w.soundcloud.com/player/?url=https%3A//soundcloud.com/@2&visual=true&sharing=false", "iframe" ],
                "idagio" => [ "/^()([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})$/", "https://app.idagio.com/player?album_id=@2", "iframe" ],
            ];
            $templates = [
                "iframe" => "<iframe title=\"@title\" width=\"100%\" class=\"sound @type @height\" src=\"@src\" frameborder=\"0\" allow=\"accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen\" loading=\"lazy\" sandbox=\"allow-scripts allow-same-origin\"@dim><p>@src</p></iframe>",
            ];
            $audioPlayerLabel = $this->yellow->language->getTextHtml("soundAudioPlayer");
            $downloadLabel = $this->yellow->language->getTextHtml("soundDownload");
            $playLabel = $this->yellow->language->getTextHtml("soundPlay");
            $timelineLabel = $this->yellow->language->getTextHtml("soundTimeline");
            $totalTimeLabel = $this->yellow->language->getTextHtml("soundTotalTime");
            $muteLabel = $this->yellow->language->getTextHtml("soundMute");
            $volumeLabel = $this->yellow->language->getTextHtml("soundVolume");
            $playlistLabel = $this->yellow->language->getTextHtml("soundPlaylist");

            foreach ($services as $audioType=>list($pattern, $sourceTemplate, $element)) {
                if (preg_match($pattern, $id, $matches)) {
                    if ($element=="custom") {
                        $showDownloadButton = $this->yellow->system->get("soundShowDownloadLink");
                        $path = $this->yellow->lookup->findMediaDirectory("soundLocation");
                        if ($audioType=="url") {
                            $meta = $this->getAudioMeta($id, true);
                            $audioType = isset($meta["list"]) ? "list" : "item";
                        }
                        $items = $audioType=="item" ? [ $id ] : $this->getPlayList($id, $meta["list"] ?? null);
                        $listId = $audioType=="list" ? $id : null;
                        $sounds = [];
                        foreach ($items as $item) {
                            $isUrl = preg_match('/^\w+:/', $item);
                            $fileName = $isUrl ? $item : $path.$item;
                            $meta = $this->getAudioMeta($fileName, $isUrl, false);
                            $src = $isUrl ? $item : $this->yellow->system->get("coreServerBase").$this->yellow->system->get("soundLocation").$item;
                            $cover = $isUrl ? null : $this->getCover($item, $listId);
                            if ($cover==null) {
                                $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
                                $coverSrc = "{$extensionLocation}sound-".(isset($meta["radio"]) ? "radio" : "default").".svg";
                            } else {
                                $coverSrc = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("soundLocation").$cover;
                            }
                            $sounds[] = [ "src"=>$src, "meta"=>$meta, "coverSrc"=>$coverSrc ];
                        }

                        $output .= "<div class=\"sound".(isset($meta["radio"]) ? " sound-radio" : "")."\" aria-label=\"".$audioPlayerLabel."\" role=\"region\">\n";
                        $output .= "<div class=\"sound-heading\">\n";
                        $output .= "<audio class=\"sound-player\" src=\"".htmlspecialchars($sounds[0]["src"])."\" preload=\"metadata\"></audio>\n";
                        $output .= "<img src=\"".htmlspecialchars($sounds[0]["coverSrc"])."\" alt=\"\" />\n";
                        $output .= "<div class=\"sound-name\">".$this->makeTitleHtml($sounds[0]["meta"], $label)."</div>\n";
                        if ($showDownloadButton) $output .= "<div class=\"sound-aux\"><a href=\"".htmlspecialchars($sounds[0]["src"])."\" class=\"sound-download\" download=\"".substr($sounds[0]["src"], strrpos($sounds[0]["src"], "/")+1)."\" aria-label=\"".$downloadLabel."\"></a></div>\n";
                        $output .= "<div class=\"sound-controls\">\n";
                        $output .= "<button class=\"sound-play\" aria-label=\"".$playLabel."\" aria-pressed=\"false\"></button>\n";
                        $output .= "<input class=\"sound-timeline\" aria-label=\"".$timelineLabel."\" type=\"range\" min=\"0\" max=\"1\" aria-valuetext=\"0:00:00\" step=\"0.01\" value=\"0\" />\n";
                        $output .= "<span class=\"sound-timedisplay\"><time class=\"sound-time\" role=\"timer\" datetime=\"0:00:00\"></time><time class=\"sound-totaltime\" aria-label=\"".$totalTimeLabel."\"></time></span>\n";
                        $output .= "<button class=\"sound-mute\" aria-label=\"".$muteLabel."\" aria-pressed=\"false\"></button>\n";
                        $output .= "<input class=\"sound-volume\" aria-label=\"".$volumeLabel."\" type=\"range\" min=\"0\" max=\"10\" step=\"1\" value=\"10\" />\n";
                        $output .= "</div>\n";
                        $output .= "</div>\n";
                        if (count($sounds)>1) {
                            $output .= "<ul class=\"sound-playlist\" aria-label=\"".$playlistLabel."\">\n";
                            foreach ($sounds as $key=>$sound) {
                                $output .= "<li ".($key==0 ? "aria-current=\"true\" " : "");
                                $output .= "data-cover=\"".htmlspecialchars($sound["coverSrc"])."\" ";
                                $output .= "data-radio=\"".(isset($meta["radio"]) ? "1" : "0")."\" ";
                                $output .= "data-src=\"".htmlspecialchars($sound["src"])."\">";
                                $output .= "<button>".$this->makeTitleHtml($sound["meta"], $label)."</button></li>\n";
                            }
                            $output .= "</ul>\n";
                        }
                        $output .= "</div>\n";
                    } else {
                        $sourceTemplate = str_replace("@lang", $page->get("language"), $sourceTemplate);
                        $sourceTemplate = strtr($sourceTemplate, array_combine([ "@0", "@1", "@2", "@3" ], array_pad($matches, 4, "")));
                        $template = str_replace([ "@title", "@type", "@height", "@src" ], [ $audioPlayerLabel, "sound-".$audioType, $matches[1], htmlspecialchars($sourceTemplate) ], $templates[$element]);
                        $output .= $template;
                    }
                    break;
                }
            }
        }
        return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}sound.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}sound.js\"></script>\n";
        }
        return $output;
    }

    // Get items from a M3U or PLS playlist
    private function getPlayList($id, $type = null) {
        $isUrl = preg_match('/^\w+:/', $id);
        $lastSlash = strrpos($id, "/");
        $prefix = $lastSlash!==false ? substr($id, 0, strrpos($id, "/")+1) : "";
        $path = $this->yellow->lookup->findMediaDirectory("soundLocation");
        $file = $isUrl ? $id : $path.$id;
        $lines = @file($file);
        if ($lines===false) return false;
        $type = $type ?? pathinfo($file, PATHINFO_EXTENSION);
        $regex = $type=="pls" ? '/^File(\d+)=(.+)$/' : '/^()([^#]+)$/';
        $list = [];
        foreach ($lines as $line) {
            if (preg_match($regex, trim($line), $matches)) {
                list($index, $src) = [ $matches[1], $matches[2] ];
                if (preg_match('/^\w+:/', $src)) $src = rawurldecode($src);
                $encoding = mb_detect_encoding($src, [ "UTF-8", "ISO-8859-1" ]);
                $src = mb_convert_encoding($src, "UTF-8", $encoding);
                if (!preg_match('/^\w+:/', $src)) $src = $prefix.$src;
                if ($index) {
                    $list[$index-1] = $src;
                } else {
                    $list[] = $src;
                }
            }
        }
        return $list;
    }

    // Get cover from the name of the audiofile or playlist
    private function getCover($audioFile, $listId) {
        $path = $this->yellow->lookup->findMediaDirectory("soundLocation");
        foreach (array_filter([ $path.$listId, $path.$audioFile ]) as $file) {
            $pathinfo = pathinfo($file);
            foreach ([ $pathinfo["dirname"]."/".$pathinfo["filename"], $pathinfo["dirname"]."/"."cover" ] as $fileName) {
                foreach ([ "jpg", "png", "svg", "gif" ] as $extension) {
                    $fullName = "$fileName.$extension";
                    if (is_file($fullName)) return substr($fullName, strlen($path));
                    // TODO extract cover in metadata
                }
            }
        }
        return null;
    }

    // Make the title from metadata and pattern
    private function makeTitleHtml($meta, $titlePattern) {
        if (isset($meta["file"])) {
            return "<b>".$meta["file"]."</b>";
        } elseif (isset($meta["radio"])) {
            if (is_string_empty($titlePattern)) $titlePattern = $this->yellow->system->get("soundDefaultTitleRadio");
            return str_replace("@radio", $meta["radio"], $titlePattern);
        } else {
            if (is_string_empty($titlePattern)) $titlePattern = $this->yellow->system->get("soundDefaultTitle");
            return preg_replace_callback('/@([a-z]+)/', function($key) use ($meta) { return $meta[$key[1]] ?? "—"; }, $titlePattern); // TODO better interpolation when keys are missing
        }
    }

    // Get the metadata, possibly from the cache
    private function getAudioMeta($audioFile, $isUrl, $canBePlaylist = true) {
        $liveStreamingLabel = $this->yellow->language->getTextHtml("soundLiveStreaming");
        if ($isUrl) {
            $cache = [];
            $cacheFileName = $this->yellow->system->get("coreExtensionDirectory")."sound.json";
            $fileHandle = @fopen($cacheFileName, "r");
            if ($fileHandle) {
                $cache = json_decode(fread($fileHandle, fstat($fileHandle)["size"]), true);
                fclose($fileHandle);
            }
            if (!isset($cache[$audioFile])) {
                $cache[$audioFile] = $this->decodeAudioMetaFromUrl($audioFile, $canBePlaylist);
                if ($cache[$audioFile]!==false) {
                    $fileHandle = @fopen($cacheFileName, "w");
                    if ($fileHandle) {
                        if (flock($fileHandle, LOCK_EX)) {
                            fwrite($fileHandle, json_encode($cache, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                        }
                        fclose($fileHandle);
                    } else {
                        $this->toolbox->log("error", "Can't write file '$cacheFileName'!");
                    }
                }
            }
            return $cache[$audioFile];
        } else {
            return $this->decodeAudioMeta($audioFile);
        }
    }

    // Detect type of remote file and possibly extract metadata
    private function decodeAudioMetaFromUrl($audioFile, $canBePlaylist = true) {
        $extensions = [
            "audio/mpeg"=>"mp3",
            "audio/opus"=>"opus",
            "audio/x-opus"=>"opus",
            "audio/x-opus+ogg"=>"opus",
            "audio/vorbis"=>"ogg",
            "audio/x-vorbis"=>"ogg",
            "audio/x-vorbis+ogg"=>"ogg",
            "audio/flac"=>"flac",
            "audio/x-flac"=>"flac",
            "audio/mp4"=>"m4a",
            "audio/aac"=>"m4a",
            "audio/aacp"=>"m4a",
            "audio/wav"=>"wav",
            "audio/wave"=>"wav",
            "audio/vnd.wav"=>"wav",
            "audio/x-wav"=>"wav",
            "audio/scpls"=>"pls",
            "audio/x-scpls"=>"pls",
            "audio/mpeg-url"=>"m3u",
            "audio/x-mpegurl"=>"m3u",
        ];
        $liveStreamingLabel = $this->yellow->language->getTextHtml("soundLiveStreaming");
        $url = $audioFile;
        $context = stream_context_create([ "http"=>[ "follow_location"=>0 ] ]);
        for ($redirections = 0; $url && $redirections<3; $redirections++) {
            $headers = @get_headers($url, true, $context);
            if ($headers===false) return false;
            $headers = array_combine(array_map("strtolower", array_keys($headers)), $headers);
            $url = $headers["location"] ?? null;
        }
        if (!isset($headers["content-length"])) {
            return [ "radio"=> ($headers["icy-name"] ?? $liveStreamingLabel) ];
        } else {
            $ext = $extensions[$headers["content-type"] ?? null] ?? null;
            if ($ext==null) {
                return false;
            } elseif (in_array($ext, [ "m3u", "pls" ])) {
                return $canBePlaylist ? [ "list"=>$ext ] : false;
            } else {
                return $this->decodeAudioMeta($audioFile, $ext);
            }
        }
    }

    // Decode metadata according the audiofile type
    public function decodeAudioMeta($file, $ext = null) {
        $ext = $ext ?? pathinfo($file, PATHINFO_EXTENSION);
        switch ($ext) {
            case "mp3":
                $meta = $this->getId3v2($file) ?? $this->getApeTag($file, false) ?? $this->getApeTag($file, true) ?? $this->getId3v1($file) ?? $this->getTagsFromFileName($file); break;
            case "opus":
                $meta = $this->getOggComment($file, "OpusTags") ?? $this->getTagsFromFileName($file); break;
            case "ogg":
                $meta = $this->getOggComment($file, "\3vorbis") ?? $this->getTagsFromFileName($file); break;
            case "flac":
                $meta = $this->getFlacComment($file) ?? $this->getTagsFromFileName($file); break;
            case "m4a":
                $meta = $this->getM4aMetadata($file) ?? $this->getTagsFromFileName($file); break;
            case "wav":
                $meta = $this->getTagsFromFileName($file); break;
            default:
                $meta = [ "file"=>pathinfo($file, PATHINFO_FILENAME) ];
        }
        $meta = $this->normaliseTags($meta);
        return $meta;
    }

    // Get ID3 v2 metadata
    private function getId3v2($file) {
        try {
            $head = $this->getChunk($file, 2000);
            if (substr($head, 0, 3)!=="ID3") return null;
            $separatorRegex = '/\s*\/\s*/';
            $allowedFrames = [
                "TP1"=>"artist", // v. 2
                "TCM"=>"composer",
                "TAL"=>"album",
                "TT2"=>"title",
                "TT3"=>"subtitle",
                "TPA"=>"disc",
                "TRK"=>"track",
                "TYE"=>"date",
                "TCO"=>"genre",
                "TPE1"=>"artist", // v. 3
                "TCOM"=>"composer",
                "TALB"=>"album",
                "TIT2"=>"title",
                "TIT3"=>"subtitle",
                "TPOS"=>"disc",
                "TRCK"=>"track",
                "TYER"=>"date",
                "TCON"=>"genre",
                "TDRC"=>"date", // v. 4
            ];
            $position = 3;
            $version = $this->scan("C", $head, $position, 1+1)[1]; // 2, 3 or 4
            if ($version<2 || $version>4) return false;
            $flags = $this->scan("C", $head, $position, 1)[1];
            $isTagUnsynchronised = $flags & (1 << 7);
            // TODO apply unsynchronising also to extended header
            $isCompressed = $version==2 && ($flags & (1 << 6));
            if ($isCompressed) return false;
            $isExtendedHeader = $version>=3 && ($flags & (1 << 6));
            $isFooter = $version==4 && ($flags & (1 << 4));
            $sizeParts = $this->scan("C4", $head, $position, 4);
            if (array_sum(array_map(function($i) { return $i >> 7; }, $sizeParts))) return false;
            $size = $sizeParts[1] << 21 | $sizeParts[2] << 14 | $sizeParts[3] << 7 | $sizeParts[4];
            if ($isExtendedHeader) {
                $extendedSizeParts = $this->scan("C4", $head, $position, 4);
                if (array_sum(array_map(function($i) { return $i >> 7; }, $extendedSizeParts))) return false;
                $extendedSize = $extendedSizeParts[1] << 21 | $extendedSizeParts[2] << 14 | $extendedSizeParts[3] << 7 | $sizeParts[4];
                $this->scan(null, $head, $position, $extendedSize);
            }
            $frameIdLenght = $version==2 ? 3 : 4;
            $meta = [];
            // TODO SEEK frame in v. 4
            while ($position<$size+10 && strlen($head)>$position && $head[$position]!=="\0") {
                $frameId = $this->scan("a".$frameIdLenght, $head, $position, $frameIdLenght)[1];
                if ($version==2) {
                    $frameSizeBytes = $this->scan("a3", $head, $position, 3)[1];
                    $frameSize = unpack("N", "\0".$frameSizeBytes)[1];
                    $isFrameCompressed = $isFrameUnsynchronised = false;
                } else {
                    $frameSize = $this->scan("N", $head, $position, 4)[1];
                    $flags = $this->scan("C2", $head, $position, 2);
                    $isFrameCompressed = $flags[2] & (1 << 3);
                    $isFrameEncrypted = $flags[2] & (1 << 2);
                    if ($isFrameEncrypted) return false;
                    $isFrameUnsynchronised = $flags[2] & (1 << 1);
                    $isDataLength = $flags[2] & 1;
                    if ($isDataLength || $isFrameCompressed) $this->scan(null, $head, $position, 4);
                }
                if (isset($allowedFrames[$frameId])) {
                    $encodingCode = $this->scan("C", $head, $position, 1)[1];
                    $encoding = [ "ISO-8859-1", "UTF-16", "UTF-16BE", "UTF-8" ][$encodingCode];
                    $information = $this->scan("a".($frameSize-1), $head, $position, $frameSize-1)[1];
                    if ($isFrameUnsynchronised || $isTagUnsynchronised) $information = str_replace("\xff\x00", "\xff", $information);
                    if ($isFrameCompressed) $information = gzinflate($information, 10000);
                    $information = mb_convert_encoding($information, "UTF-8", $encoding);
                    if ($version==4) {
                        $information = str_replace("\0", $this->separator, trim($information));
                    } elseif (in_array($frameId, [ "TP1", "TCM", "TPE1", "TCOM" ])) {
                        $information = preg_replace($separatorRegex, $this->separator, $information);
                    }
                    $meta[$allowedFrames[$frameId]] = $information;
                } else {
                    $this->scan(null, $head, $position, $frameSize);
                }
            }
            if (isset($meta["genre"])) {
                $meta["genre"] = trim(preg_replace_callback($version<4 ? '/\((\d+|RX|CR)\)\s?/' : '/(\d+|RX|CR)/', function($id) { return ($this->getGenre($id[1]) ?? $id[0])." "; }, $meta["genre"]));
            }
            return $meta;
        } catch (Exception $e) {
            return $meta;
        }
    }

    // Get Flac comment (encoded as Vorbis comment)
    private function getFlacComment($file) {
        try {
            $head = $this->getChunk($file, 4000);
            if (substr($head, 0, 4)!=="fLaC") return null;
            $position = 4;
            $isLastBlock = false;
            while (!$isLastBlock) {
                $flags = $this->scan("C4", $head, $position, 4);
                $isLastBlock = (bool)($flags[1] & (1 << 7));
                $blockType = $flags[1] & 127;
                $blockLength = $flags[2] << 16 | $flags[3] << 8 | $flags[4];
                if ($blockType==4) {
                    return $this->decodeVorbis(substr($head, $position, $blockLength));
                }
                $this->scan(null, $head, $position, $blockLength);
            }
            return false;
        } catch (Exception $e) {
            return null;
        }
    }

    // Get Ogg/Vorbis comment (encoded as Vorbis comment)
    private function getOggComment($file, $signature) {
        try {
            $head = $this->getChunk($file, 4000);
            if (substr($head, 0, 4)!=="OggS") return null;
            $position = $pageLength = 0;
            foreach ([1, 2] as $page) {
                $this->scan(null, $head, $position, $pageLength+26);
                $segmentsNumber = $this->scan("C", $head, $position, 1)[1];
                $segmentsLengths = $this->scan("C".$segmentsNumber, $head, $position, $segmentsNumber);
                $pageLength = array_sum($segmentsLengths);
            }
            if ($signature==substr($head, $position, strlen($signature))) {
                return $this->decodeVorbis(substr($head, $position+strlen($signature), $pageLength-strlen($signature)));
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    // Decode Vorbis comment
    private function decodeVorbis($chunk) {
        try {
            $allowedFields = [
                "artist"=>"artist",
                "composer"=>"composer",
                "performer"=>"performer",
                "album"=>"album",
                "work"=>"work",
                "title"=>"title",
                "discnumber"=>"disc",
                "tracknumber"=>"track",
                "date"=>"date",
                "genre"=>"genre",
            ];
            $meta = [];
            $position = 0;
            $vendorLength = $this->scan("V", $chunk, $position, 4)[1];
            $this->scan(null, $chunk, $position, $vendorLength);
            $commentListLength = $this->scan("V", $chunk, $position, 4)[1];
            while ($commentListLength-->0) {
                $commentLength = $this->scan("V", $chunk, $position, 4)[1];
                $commentContent = $this->scan("a".$commentLength, $chunk, $position, $commentLength)[1];
                list($name, $value) = explode("=", $commentContent, 2);
                $name = strtolower($name);
                if (isset($allowedFields[$name])) {
                    $meta[$allowedFields[$name]][] = $value;
                }
            }
            foreach ($meta as &$metaItem) {
                $metaItem = implode($this->separator, $metaItem);
            }
            return $meta;
        } catch (Exception $e) {
            return $meta;
        }
    }

    // Get M4A metadata (Apple extension)
    private function getM4aMetadata($file) {
        try {
            $head = $this->getChunk($file, 128000);
            if (substr($head, 4, 4)!=="ftyp") return null;
            $hierarchy = [ "moov", "udta", "meta", "ilst", null ];
            $allowedKeys = [
                "\xa9ART"=>"artist",
                "\xa9wrt"=>"composer",
                "\xa9alb"=>"album",
                "\xa9wrk"=>"work",
                "\xa9nam"=>"title",
                "disk"=>"disc",
                "trkn"=>"track",
                "\xa9day"=>"date",
                "\xa9gen"=>"genre",
            ];
            $currentLevel = $position = 0;
            $limit = strlen($head);
            $meta = [];
            while ($position<$limit) {
                $length = $this->scan("N", $head, $position, 4)[1];
                $name = $this->scan("a4", $head, $position, 4)[1];
                $endOfAtom = $position+$length-8;
                if ($name==$hierarchy[$currentLevel]) {
                    $currentLevel += 1;
                    $limit = $endOfAtom;
                    if ($name=="meta") $position += 4;
                } else {
                    if ($currentLevel==4 && isset($allowedKeys[$name])) {
                        // TODO check on $head length
                        $value = $this->getM4aDataValue($name, substr($head, $position, $length-8));
                        if (isset($value)) $meta[$allowedKeys[$name]] = $value;
                    }
                    $this->scan(null, $head, $position, $endOfAtom-$position);
                }
            }
            return $meta;
        } catch (Exception $e) {
            return $meta;
        }
    }

    // Get a single M4A data value
    private function getM4aDataValue($atomName, $dataTag) {
        try {
            $position = 0;
            $length = $this->scan("N", $dataTag, $position, 4)[1];
            $name = $this->scan("a4", $dataTag, $position, 4)[1];
            if ($name!=="data") return false;
            $flags = $this->scan("C4", $dataTag, $position, 4);
            $valueClass = $flags[2] << 16 | $flags[3] << 8 | $flags[4];
            $this->scan(null, $dataTag, $position, 4);
            if ($valueClass==0 && ($atomName=="trkn" || $atomName=="disk")) {
                $this->scan(null, $dataTag, $position, 2);
                $value = implode("/", $this->scan("n2", $dataTag, $position, 4));
            } elseif ($valueClass==1) {
                $value = str_replace("\0", $this->separator, trim($this->scan("a".($length-16), $dataTag, $position, $length-16)[1]));
            } else {
                $value = null;
            }
            return $value;
        } catch (Exception $e) {
            return null;
        }
    }

    // Get APE metadata
    private function getApeTag($file, $isHead) {
        try {
            $chunk = $this->getChunk($file, $isHead ? 2000 : -2000);
            if (substr($chunk, $isHead ? 0 : -32, 8)!=="APETAGEX") return null;
            $allowedTags = [
                "artist"=>"artist",
                "composer"=>"composer",
                "album"=>"album",
                "title"=>"title",
                "subtitle"=>"subtitle",
                "disc"=>"disc",
                "track"=>"track",
                "year"=>"date",
                "genre"=>"genre",
            ];
            // TODO: when !$isHead tag could be before id3v1
            $position = $isHead ? 0 : strlen($chunk)-32;
            $this->scan(null, $chunk, $position, 8);
            $version = $this->scan("V", $chunk, $position, 4)[1];
            if ($version!==1000 && $version!==2000) return false;
            $tagSize = $this->scan("V", $chunk, $position, 4)[1];
            $itemCount = $this->scan("V", $chunk, $position, 4)[1];
            $tagFlags = $this->scan("V", $chunk, $position, 4+8)[1];
            if ((bool)($tagFlags >> 2 & 1)!==$isHead) return false;
            if (!$isHead) $this->scan(null, $chunk, $position, -$tagSize);
            $meta = [];
            while ($itemCount-->0) {
                $itemSize = $this->scan("V", $chunk, $position, 4)[1];
                $itemFlags = $this->scan("V", $chunk, $position, 4)[1];
                $isItemText = $itemFlags >> 29 & 3 == 0;
                $key = $this->scan("Z*", $chunk, $position, 0)[1];
                $this->scan(null, $chunk, $position, strlen($key)+1);
                $value = $this->scan("a".$itemSize, $chunk, $position, $itemSize)[1];
                if ($isItemText && isset($allowedTags[strtolower($key)])) {
                     $meta[$allowedTags[strtolower($key)]] = str_replace("\0", $this->separator, trim($value));
                }
            }
            return $meta;
        } catch (Exception $e) {
            return $meta;
        }
    }

    // Get ID3 v1 (legacy) metadata
    private function getId3v1($file) {
        $tail = $this->getChunk($file, -256);
        if (substr($tail, 128, 3)!=="TAG") return null;
        if (substr($tail, 253, 1)=="\0" && substr($tail, 254, 1)!="\0") {
            $meta = unpack("A30title/A30artist/A30album/A4date/A28comment/n1track/C1genre", $tail, 131); // v. 1.1
            $meta["track"] = (string)$meta["track"];
        } else {
            $meta = unpack("A30title/A30artist/A30album/A4date/A30comment/C1genre", $tail, 131);
        }
        if (substr($tail, 0, 3)=="EXT") { // v1.2
            $extendedMeta = unpack("A30title/A30artist/A30album/A15comment", $tail, 3);
            foreach ($extendedMeta as $tag=>$value) {
                $meta[$tag] .= $value;
            }
        }
        foreach ($meta as $tag=>$value) {
            $meta[$tag] = preg_match('//u', $value) ? $value : mb_convert_encoding($value, "UTF-8", "ISO-8859-1");
        }
        $meta["genre"] = $this->getGenre($meta["genre"]);
        unset($meta["comment"]);
        return array_filter($meta, "strlen");
    }

    // Get metadata from filename, according to a pattern
    private function getTagsFromFileName($file) {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $fileNamePattern = $this->yellow->system->get("soundFileNamePattern");
        $pattern = preg_replace('/@([a-z]+)/', '(?<$1>.+?)', preg_quote($fileNamePattern));
        if (preg_match("|^{$pattern}$|", $fileName, $matches)) {
            $whiteList = array_flip($this->soundFieldList);
            return array_intersect_key($matches, $whiteList);
        }
        return [ "file"=>$fileName ];
    }

    // Unpack data and advance pointer
    private function scan($pattern, $string, &$position, $length) {
        if ($position+$length>strlen($string)) throw new Exception("Too few characters.");
        $data = unpack($pattern, $string, $position);
        $position += $length;
        return $data;
    }

    // Get music genre from ID3 codes
    private function getGenre($code) {
        $genreList = [ "Blues", "Classic Rock", "Country", "Dance", "Disco", "Funk", "Grunge", "Hip-Hop", "Jazz", "Metal", "New Age", "Oldies", "Other", "Pop", "R&B", "Rap", "Reggae", "Rock", "Techno", "Industrial", "Alternative", "Ska", "Death Metal", "Pranks", "Soundtrack", "Euro-Techno", "Ambient", "Trip-Hop", "Vocal", "Jazz+Funk", "Fusion", "Trance", "Classical", "Instrumental", "Acid", "House", "Game", "Sound Clip", "Gospel", "Noise", "Alt. Rock", "Bass", "Soul", "Punk", "Space", "Meditative", "Instrumental Pop", "Instrumental Rock", "Ethnic", "Gothic", "Darkwave", "Techno-Industrial", "Electronic", "Pop-Folk", "Eurodance", "Dream", "Southern Rock", "Comedy", "Cult", "Gangsta Rap", "Top 40", "Christian Rap", "Pop/Funk", "Jungle", "Native American", "Cabaret", "New Wave", "Psychedelic", "Rave", "Showtunes", "Trailer", "Lo-Fi", "Tribal", "Acid Punk", "Acid Jazz", "Polka", "Retro", "Musical", "Rock & Roll", "Hard Rock", "Folk", "Folk-Rock", "National Folk", "Swing", "Fast-Fusion", "Bebop", "Latin", "Revival", "Celtic", "Bluegrass", "Avantgarde", "Gothic Rock", "Progressive Rock", "Psychedelic Rock", "Symphonic Rock", "Slow Rock", "Big Band", "Chorus", "Easy Listening", "Acoustic", "Humour", "Speech", "Chanson", "Opera", "Chamber Music", "Sonata", "Symphony", "Booty Bass", "Primus", "Porn Groove", "Satire", "Slow Jam", "Club", "Tango", "Samba", "Folklore", "Ballad", "Power Ballad", "Rhythmic Soul", "Freestyle", "Duet", "Punk Rock", "Drum Solo", "A Cappella", "Euro-House", "Dance Hall", "Goa", "Drum & Bass", "Club-House", "Hardcore", "Terror", "Indie", "BritPop", "Afro-Punk", "Polsk Punk", "Beat", "Christian Gangsta Rap", "Heavy Metal", "Black Metal", "Crossover", "Contemporary Christian", "Christian Rock", "Merengue", "Salsa", "Thrash Metal", "Anime", "JPop", "Synthpop", "Abstract", "Art Rock", "Baroque", "Bhangra", "Big Beat", "Breakbeat", "Chillout", "Downtempo", "Dub", "EBM", "Eclectic", "Electro", "Electroclash", "Emo", "Experimental", "Garage", "Global", "IDM", "Illbient", "Industro-Goth", "Jam Band", "Krautrock", "Leftfield", "Lounge", "Math Rock", "New Romantic", "Nu-Breakz", "Post-Punk", "Post-Rock", "Psytrance", "Shoegaze", "Space Rock", "Trop Rock", "World Music", "Neoclassical", "Jouelebook", "Jouele Theatre", "Neue Deutsche Welle", "Podcast", "Indie Rock", "G-Funk", "Dubstep", "Garage Rock", "Psybient", /* ID3v2 additions */ "RX"=>"Remix", "CR"=>"Cover" ];
        return $genreList[$code] ?? null;
    }

    // Get a chunk from a local or remote file
    private function getChunk($file, $length) {
        if (preg_match('/^\w+:/', $file)) {
            if ($length<0) $fileSize = $this->getRemoteFileSize($file);
            $chunk = $this->getRemoteFileRange($file, $length>0 ? 0 : $fileSize+$length, $length>0 ? $length-1 : $fileSize-1);
            if (strlen($chunk)>$length) $chunk = substr($chunk, $length>0 ? 0 : $length, abs($length));
        } else {
            $fileHandle = @fopen($file, "r");
            if ($fileHandle==false) return null; // TODO better checks
            if ($length<0) {
                fseek($fileHandle, fstat($fileHandle)["size"]+$length);
            }
            $chunk = fread($fileHandle, abs($length));
            fclose($fileHandle);
        }
        // TODO check if there are enough bytes if $length<0
        return $chunk;
    }

    // Get the size of a remote file
    private function getRemoteFileSize($url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HEADER=>false,
            CURLOPT_NOBODY=>1,
            CURLOPT_RETURNTRANSFER=>0,
            CURLOPT_FOLLOWLOCATION=>1,
            CURLOPT_MAXREDIRS=>3,
        ]);
        curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD) ?? null;
        curl_close($ch);
        return $fileSize;
    }

    // Get a range of bytes from a remote file
    private function getRemoteFileRange($url, $from, $to) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HEADER=>false,
            CURLOPT_RANGE=>"$from-$to",
            CURLOPT_RETURNTRANSFER=>true,
        ]);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    // Normalise audio tags, especially for classical music
    private function normaliseTags($meta) {
        $composerAsArtist = $this->yellow->system->get("soundComposerAsArtist");
        if (empty($meta)) return $meta; // if false, null or []
        $artists = [ "performer", "composer" ];
        if ($composerAsArtist) $artists = array_reverse($artists);
        if (isset($meta[$artists[0]])) {
            if (isset($meta["artist"])) $meta[$artists[1]] = $meta["artist"];
            $meta["artist"] = $meta[$artists[0]];
            unset($meta[$artists[0]]);
        }
        $meta["title"] = implode($this->separator, array_filter([
            $meta["work"] ?? null,
            $meta["title"] ?? null,
            $meta["subtitle"] ?? null,
        ], "strlen"));
        unset($meta["work"], $meta["subtitle"]);
        $orderList = array_flip($this->soundFieldList);
        uksort($meta, function($a, $b) use ($orderList) { return $orderList[$a]<=>$orderList[$b]; });
        return array_filter($meta, "strlen");
    }

}
