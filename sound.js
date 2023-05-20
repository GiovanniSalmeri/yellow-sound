// Sound extension, https://github.com/GiovanniSalmeri/yellow-sound
"use strict";

document.addEventListener("DOMContentLoaded", function() {
    function formatTime(d, total, streaming) {
        if (d==NaN || d==Infinity) return "";
        if (streaming) total = Infinity;
        var h = Math.floor(d/3600);
        var m = Math.floor(d%3600/60);
        var s = Math.floor(d%3600%60);
        if (s<10) s = "0"+s;
        if (m<10 && total>=600) m = "0"+m;
        if (h<10 && total>=36000) h = "0"+h;
        return (total>=3600 ? h+":" : "")+m+":"+s;
    }
    var sounds = document.querySelectorAll("div.sound");
    function pauseAll() {
        sounds.forEach(function(sound) {
            sound.querySelector("button.sound-play").setAttribute("aria-pressed", "false");
            sound.querySelector("audio").pause();
        });
    }

    sounds.forEach(function(sound) {

        function initTime(audioElement) {
            if (!isRadio) {
                totaltimeElement.textContent = formatTime(audioElement.duration, audioElement.duration, isRadio);
                totaltimeElement.setAttribute("datetime", formatTime(audioElement.duration, Infinity));
            }
            timeElement.textContent = formatTime(0, audioElement.duration, isRadio);
        }

        var isRadio = sound.classList.contains("sound-radio");
        var audioElement = sound.querySelector("audio");
        var playElement = sound.querySelector("button.sound-play");
        var timelineElement = sound.querySelector("input.sound-timeline");
        var timeElement = sound.querySelector("time.sound-time");
        var totaltimeElement = sound.querySelector("time.sound-totaltime");
        var muteElement = sound.querySelector("button.sound-mute");
        var volumeElement = sound.querySelector("input.sound-volume");
        var imageElement = sound.querySelector("img");
        var nameElement =  sound.querySelector("div.sound-name");
        var downloadElement =  sound.querySelector("a.sound-download");
        var mutedVolume = null;
	var currentTrack = 0;

        timelineElement.value = 0; // Firefox
        volumeElement.value = 10; // Firefox
        if (audioElement.duration!==NaN) initTime(audioElement); // Firefox

        audioElement.volume = 1;
        audioElement.addEventListener("loadedmetadata", function() {
            initTime(this);
            this.dispatchEvent(new CustomEvent("timeupdate"));
        });
        audioElement.addEventListener("timeupdate", function() {
            var ISOTime = formatTime(this.currentTime, Infinity);
            if (!isRadio) {
                timelineElement.value = this.currentTime / this.duration;
                timelineElement.setAttribute("aria-valuetext", ISOTime);
            }
            timeElement.textContent = formatTime(this.currentTime, this.duration, isRadio);
            timeElement.setAttribute("datetime", ISOTime);
        });
        audioElement.addEventListener("ended", function() {
            playElement.setAttribute("aria-pressed", "false");
            if (currentTrack<tracks.length-1) {
                setTrack(currentTrack+1);
            }
        });
        playElement.addEventListener("click", function() {
            if (audioElement.paused) {
                pauseAll();
                this.setAttribute("aria-pressed", "true");
                audioElement.play();
            } else {
                this.setAttribute("aria-pressed", "false");
                audioElement.pause();
            }
        });
        timelineElement.addEventListener("input", function() {
            audioElement.currentTime = this.value*audioElement.duration;
        });
        muteElement.addEventListener("click", function() {
            if (audioElement.muted) {
                this.setAttribute("aria-pressed", "false");
                audioElement.muted = false;
                volumeElement.value = mutedVolume;
            } else {
                this.setAttribute("aria-pressed", "true");
                audioElement.muted = true;
                mutedVolume = volumeElement.value;
                volumeElement.value = 0;
            }
        });
        volumeElement.addEventListener("input", function() {
            audioElement.volume = this.value/10;
            if (audioElement.volume==0 && !audioElement.muted) {
                muteElement.setAttribute("aria-pressed", "true");
                audioElement.muted = true;
            } else if (audioElement.volume>0 && audioElement.muted) {
                muteElement.setAttribute("aria-pressed", "false");
                audioElement.muted = false;
            }
        });

        var tracks = Array.from(sound.querySelectorAll("ul li"));
        function setTrack(i) {
            currentTrack = i;
            var src = tracks[i].dataset.src;
            audioElement.src = src;
            audioElement.load();
            if (downloadElement) {
                downloadElement.href = src;
                downloadElement.download = src.substr(src.lastIndexOf("/")+1);
            }
            imageElement.src = tracks[i].dataset.cover;
            if (tracks[i].dataset.radio=="1") {
                sound.classList.add("sound-radio");
                isRadio = true;

            } else {
                sound.classList.remove("sound-radio");
                isRadio = false;
            }
            nameElement.innerHTML = tracks[i].firstChild.innerHTML;
            tracks.forEach(function(track) {
                track.removeAttribute("aria-current");
            });
            tracks[i].setAttribute("aria-current", "true");
            pauseAll();
            playElement.setAttribute("aria-pressed", "true");
            audioElement.play();
        }
        tracks.forEach(function(track) {
            track.addEventListener("click", function() { 
                setTrack(tracks.indexOf(this));
            });
        });
    });
});
