/**
 * @copyright  (C) 2022 Richard Fath <https://www.richard-fath.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API was not properly initialized');
}

function seek(el, pos) {
  el.currentTime = pos;
  el.play();
}

function updchapter(elAudio, elStatus, playlist, txtSeeking) {
  if (elAudio.seeking) {
    elStatus.innerHTML = txtSeeking;
  } else if (elAudio.currentTime <= 0) {
    elStatus.innerHTML = '';
  } else {
    let tmpTitle = '';
    let i = playlist.length - 1;

    while (i >= 0) {
      if (elAudio.currentTime >= playlist[i].start) {
        tmpTitle = playlist[i].title;
        break;
      }

      i -= 1;
    }

    elStatus.innerHTML = tmpTitle;
  }
}

function setstatus(el, txt) {
  el.innerHTML = txt;
}

function clearstatus(el, txt) {
  if (el.innerHTML === txt) el.innerHTML = '';
}

const allAudioPlayerDivs = document.querySelectorAll('div.rfaudioplayer');
allAudioPlayerDivs.forEach((audioPlayerDiv) => {
  const myAudio = audioPlayerDiv.getElementsByTagName('audio')[0];
  const myStatus = audioPlayerDiv.querySelector('.rfaudiostatus');
  const showStatus = myStatus ? (!!myStatus.getAttribute('data-show-status')) : false;
  const showTitle = myStatus ? (!!myStatus.getAttribute('data-show-title')) : false;
  const myPlaylistItems = audioPlayerDiv.getElementsByTagName('li');

  let textSeeking = '';

  if (showStatus) {
    const textLoading = Joomla.Text._('MOD_RFAUDIO_LOADING').replace('&hellip;', '\u{2026}');

    textSeeking = Joomla.Text._('MOD_RFAUDIO_SEEKING').replace('&hellip;', '\u{2026}');

    myAudio.addEventListener('loadstart', () => {
      if (myAudio.networkState === 2) {
        setstatus(myStatus, textLoading);
      }
    });
    myAudio.addEventListener('waiting', () => {
      if (myAudio.networkState === 2) {
        setstatus(myStatus, textLoading);
      }
    });
    myAudio.addEventListener('canplay', () => {
      clearstatus(myStatus, textLoading);
    });
    myAudio.addEventListener('playing', () => {
      clearstatus(myStatus, textLoading);
    });
    myAudio.addEventListener('seeking', () => {
      setstatus(myStatus, textSeeking);
    });
    myAudio.addEventListener('seeked', () => {
      clearstatus(myStatus, textSeeking);
    });
  }

  if (showTitle) {
    const myPlaylist = [];

    for (let i = 0; i < myPlaylistItems.length; i += 1) {
      const myPlaylistItem = myPlaylistItems[i].getElementsByTagName('button')[0];
      const item = {
        start: parseFloat(myPlaylistItem.getAttribute('data-start')).toFixed(1),
        title: myPlaylistItem.innerHTML,
      };
      myPlaylist[i] = item;
      myPlaylistItem.addEventListener('click', () => {
        seek(myAudio, item.start);
      });
    }

    myPlaylist[myPlaylistItems.length] = {
      start: myAudio.duration,
      title: '',
    };
    myAudio.addEventListener('durationchange', () => {
      myPlaylist[myPlaylistItems.length].start = myAudio.duration;
    });
    myAudio.addEventListener('timeupdate', () => {
      updchapter(myAudio, myStatus, myPlaylist, textSeeking);
    });
  } else {
    for (let i = 0; i < myPlaylistItems.length; i += 1) {
      const myPlaylistItem = myPlaylistItems[i].getElementsByTagName('button')[0];
      myPlaylistItem.addEventListener('click', () => {
        seek(myAudio, parseFloat(myPlaylistItem.getAttribute('data-start')).toFixed(1));
      });
    }
  }
});
