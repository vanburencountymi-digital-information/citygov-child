document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('audio');
    if (!audio) return; // In case the player isn't on the page
  
    const playPauseBtn = document.getElementById('playPause');
    const playIcon = playPauseBtn.querySelector('.play-icon');
    const pauseIcon = playPauseBtn.querySelector('.pause-icon');
    const skipBackBtn = document.getElementById('skipBack');
    const skipForwardBtn = document.getElementById('skipForward');
    const progressBar = document.getElementById('progressBar');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl = document.getElementById('duration');
    const volumeSlider = document.getElementById('volumeSlider');
    const speedSelector = document.getElementById('speedSelector');
  
    function formatTime(seconds) {
      const min = Math.floor(seconds / 60);
      const sec = Math.floor(seconds % 60);
      return `${min}:${sec < 10 ? '0' + sec : sec}`;
    }
    
    function updateProgressBarStyle(value, max) {
        const percentage = (value / max) * 100;
        progressBar.style.backgroundSize = `${percentage}% 100%`;
    }

    // Set initial values
    progressBar.min = 0;
    
    // Force metadata loading if needed
    if (audio.readyState === 0) {
      audio.load();
    }
    
    // Multiple event listeners to ensure we catch the duration in all scenarios
    function setupAudioDuration() {
      if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
        const duration = audio.duration + 0.5;
        progressBar.max = duration.toString();
        durationEl.textContent = formatTime(audio.duration);
        updateProgressBarStyle(audio.currentTime, duration);
        console.log("Duration set to:", audio.duration);
      } else {
        console.log("Duration not available yet");
      }
    }
    
    // Try to get duration immediately if already loaded
    setupAudioDuration();
    
    // Listen for the loadedmetadata event
    audio.addEventListener('loadedmetadata', () => {
      setupAudioDuration();
    });
    
    // Also listen for durationchange as a backup
    audio.addEventListener('durationchange', () => {
      setupAudioDuration();
    });
    
    // And canplay as another fallback
    audio.addEventListener('canplay', () => {
      setupAudioDuration();
    });
  
    audio.addEventListener('timeupdate', () => {
      const currentTime = audio.currentTime;
      progressBar.value = currentTime.toString();
      currentTimeEl.textContent = formatTime(currentTime);
      
      // Check if we have a valid duration, if not try to set it again
      if (!progressBar.max || progressBar.max === "0" || progressBar.max === "NaN") {
        setupAudioDuration();
      }
      
      const duration = parseFloat(progressBar.max) || audio.duration + 0.5;
      updateProgressBarStyle(currentTime, duration);
    });
  
    playPauseBtn.addEventListener('click', () => {
      if (audio.paused) {
        // Try to load metadata again if needed
        if (audio.readyState === 0) {
          audio.load();
        }
        
        const playPromise = audio.play();
        
        if (playPromise !== undefined) {
          playPromise.then(() => {
            playIcon.style.display = 'none';
            pauseIcon.style.display = 'block';
          }).catch(error => {
            console.error("Play failed:", error);
          });
        }
      } else {
        audio.pause();
        playIcon.style.display = 'block';
        pauseIcon.style.display = 'none';
      }
    });
  
    skipBackBtn.addEventListener('click', () => {
      audio.currentTime -= 15;
    });
  
    skipForwardBtn.addEventListener('click', () => {
      audio.currentTime += 15;
    });
  
    progressBar.addEventListener('input', () => {
      audio.currentTime = parseFloat(progressBar.value);
      const duration = parseFloat(progressBar.max) || audio.duration + 0.5;
      updateProgressBarStyle(parseFloat(progressBar.value), duration);
    });
  
    volumeSlider.addEventListener('input', () => {
      audio.volume = volumeSlider.value;
    });
  
    speedSelector.addEventListener('change', () => {
      audio.playbackRate = speedSelector.value;
    });
  
    audio.addEventListener('ended', () => {
      playIcon.style.display = 'block';
      pauseIcon.style.display = 'none';
    });
  });
  