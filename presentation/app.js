// BTS Presentation Application Engine
// Supports Progressive In-Slide Step Reveals & Dual-Screen Presenter Synchronization

document.addEventListener('DOMContentLoaded', () => {
  const slides = document.querySelectorAll('.slide');
  const totalSlides = slides.length;
  let currentSlideIndex = 0;
  let currentStepIndex = 0;
  
  const progressBar = document.getElementById('progressBar');
  const currentSlideDisplay = document.getElementById('currentSlideNum');
  const totalSlidesDisplay = document.getElementById('totalSlidesNum');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const fullscreenBtn = document.getElementById('fullscreenBtn');
  const notesBtn = document.getElementById('notesBtn');
  const presenterBtn = document.getElementById('presenterBtn');
  const notesDrawer = document.getElementById('notesDrawer');
  const notesContent = document.getElementById('notesContent');
  const notesSpeaker = document.getElementById('notesSpeaker');
  
  if (totalSlidesDisplay) {
    totalSlidesDisplay.textContent = totalSlides;
  }
  
  // Setup BroadcastChannel for dual-screen presenter synchronization
  // Setup BroadcastChannel for dual-screen presenter synchronization
  let channel = null;
  let lastActionId = null;
  let lastActionTimestamp = 0;

  function broadcastState() {
    const steps = getActiveSteps();
    const statePayload = { 
      slideIndex: currentSlideIndex, 
      totalSlides: totalSlides, 
      stepIndex: currentStepIndex, 
      maxSteps: steps.length,
      timestamp: Date.now()
    };
    if (channel) {
      channel.postMessage(statePayload);
    }
    try {
      localStorage.setItem('bts_current_slide', currentSlideIndex.toString());
      localStorage.setItem('bts_current_state', JSON.stringify(statePayload));
    } catch (e) {}
  }

  function handleRemoteAction(data) {
    if (!data) return;

    // Deduplicate identical action IDs (received via both BroadcastChannel and localStorage)
    if (data.id && data.id === lastActionId) {
      return;
    }

    // Debounce actions firing in quick succession (under 80ms)
    const now = Date.now();
    if (now - lastActionTimestamp < 80) {
      return;
    }

    if (data.id) lastActionId = data.id;
    lastActionTimestamp = now;

    if (data.action === 'next') {
      advance();
    } else if (data.action === 'prev') {
      rewind();
    } else if (data.action === 'sync') {
      broadcastState();
    } else if (typeof data.slideIndex === 'number') {
      goToSlide(data.slideIndex, false);
    }
  }

  try {
    channel = new BroadcastChannel('bts_defense_channel');
    channel.onmessage = (event) => {
      handleRemoteAction(event.data);
    };
  } catch (e) {
    console.warn('BroadcastChannel error:', e);
  }

  // Cross-tab / cross-window localStorage fallback
  window.addEventListener('storage', (e) => {
    if (e.key === 'bts_presenter_action') {
      try {
        const data = JSON.parse(e.newValue);
        handleRemoteAction(data);
      } catch (err) {}
    } else if (e.key === 'bts_current_slide') {
      goToSlide(parseInt(e.newValue, 10), false);
    }
  });
  
  function getActiveSteps() {
    const activeSlide = slides[currentSlideIndex];
    return activeSlide ? activeSlide.querySelectorAll('.step-item') : [];
  }
  
  function updateSlideState(resetSteps = true) {
    slides.forEach((slide, index) => {
      if (index === currentSlideIndex) {
        slide.classList.add('active');
        slide.scrollTop = 0;
      } else {
        slide.classList.remove('active');
      }
    });
    
    // Manage step reveals
    const steps = getActiveSteps();
    if (resetSteps) {
      currentStepIndex = 0;
      steps.forEach(step => step.classList.remove('revealed'));
      // Reveal the first step by default if steps exist
      if (steps.length > 0) {
        steps[0].classList.add('revealed');
        currentStepIndex = 1;
      }
    }
    
    // Update counter & progress bar
    if (currentSlideDisplay) {
      currentSlideDisplay.textContent = currentSlideIndex + 1;
    }
    if (progressBar) {
      const percentage = ((currentSlideIndex + 1) / totalSlides) * 100;
      progressBar.style.width = `${percentage}%`;
    }
    
    // Update speaker notes drawer
    const activeSlide = slides[currentSlideIndex];
    if (activeSlide) {
      const noteData = activeSlide.getAttribute('data-notes') || 'No speaker notes for this slide.';
      const speakerData = activeSlide.getAttribute('data-speaker') || 'Presenter';
      if (notesContent) notesContent.innerHTML = noteData;
      if (notesSpeaker) notesSpeaker.textContent = speakerData;
    }
    
    // Broadcast state to presenter window
    broadcastState();
  }
  
  function goToSlide(index, resetSteps = true) {
    if (index >= 0 && index < totalSlides) {
      currentSlideIndex = index;
      updateSlideState(resetSteps);
    }
  }
  
  function advance() {
    const steps = getActiveSteps();
    if (steps.length > 0 && currentStepIndex < steps.length) {
      // Reveal next step inside current slide
      steps[currentStepIndex].classList.add('revealed');
      currentStepIndex++;
      broadcastState();
    } else {
      // Move to next slide
      if (currentSlideIndex < totalSlides - 1) {
        goToSlide(currentSlideIndex + 1, true);
      }
    }
  }
  
  function rewind() {
    const steps = getActiveSteps();
    if (steps.length > 0 && currentStepIndex > 1) {
      // Unreveal current step
      currentStepIndex--;
      steps[currentStepIndex].classList.remove('revealed');
      broadcastState();
    } else {
      // Go to previous slide and reveal all its steps
      if (currentSlideIndex > 0) {
        currentSlideIndex--;
        updateSlideState(false);
        const prevSteps = getActiveSteps();
        currentStepIndex = prevSteps.length;
        prevSteps.forEach(s => s.classList.add('revealed'));
        broadcastState();
      }
    }
  }
  
  function toggleFullscreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(err => {
        console.error(`Error attempting to enable fullscreen: ${err.message}`);
      });
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      }
    }
  }
  
  function toggleNotesDrawer() {
    if (notesDrawer) {
      notesDrawer.classList.toggle('open');
    }
  }
  
  function openPresenterWindow() {
    const presenterWindow = window.open('presenter.html', 'BTSPresenterWindow', 'width=1100,height=750');
    if (presenterWindow) {
      presenterWindow.focus();
    }
  }
  
  // Lightbox Image Zoom Functionality
  const lightboxModal = document.getElementById('lightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');

  function openLightbox(src, captionText) {
    if (!lightboxModal || !lightboxImg) return;
    lightboxImg.src = src;
    if (lightboxCaption) {
      lightboxCaption.textContent = captionText || '';
      lightboxCaption.style.display = captionText ? 'block' : 'none';
    }
    lightboxModal.classList.add('active');
  }

  function closeLightbox() {
    if (!lightboxModal) return;
    lightboxModal.classList.remove('active');
    setTimeout(() => {
      if (lightboxImg && !lightboxModal.classList.contains('active')) {
        lightboxImg.src = '';
      }
    }, 300);
  }

  // Attach click listener to all screenshots
  document.querySelectorAll('.screenshot-container').forEach(container => {
    container.addEventListener('click', (e) => {
      const img = container.querySelector('img');
      if (img) {
        const captionEl = container.querySelector('.screenshot-caption') || container.querySelector('.screenshot-badge');
        const caption = captionEl ? captionEl.textContent.trim() : (img.alt || '');
        openLightbox(img.src, caption);
      }
    });
  });

  if (lightboxModal) {
    lightboxModal.addEventListener('click', closeLightbox);
  }

  // Event Listeners for Buttons
  if (nextBtn) nextBtn.addEventListener('click', advance);
  if (prevBtn) prevBtn.addEventListener('click', rewind);
  if (fullscreenBtn) fullscreenBtn.addEventListener('click', toggleFullscreen);
  if (notesBtn) notesBtn.addEventListener('click', toggleNotesDrawer);
  if (presenterBtn) presenterBtn.addEventListener('click', openPresenterWindow);
  
  // Keyboard Navigation
  document.addEventListener('keydown', (e) => {
    // If lightbox is open, Escape or any click closes it
    if (lightboxModal && lightboxModal.classList.contains('active')) {
      if (e.key === 'Escape' || e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        closeLightbox();
        return;
      }
    }

    switch (e.key) {
      case 'ArrowRight':
      case 'ArrowDown':
      case 'PageDown':
      case ' ':
        e.preventDefault();
        advance();
        break;
      case 'ArrowLeft':
      case 'ArrowUp':
      case 'PageUp':
        e.preventDefault();
        rewind();
        break;
      case 'Home':
        e.preventDefault();
        goToSlide(0, true);
        break;
      case 'End':
        e.preventDefault();
        goToSlide(totalSlides - 1, true);
        break;
      case 'f':
      case 'F':
        e.preventDefault();
        toggleFullscreen();
        break;
      case 'n':
      case 'N':
        e.preventDefault();
        toggleNotesDrawer();
        break;
      case 'p':
      case 'P':
        e.preventDefault();
        openPresenterWindow();
        break;
      case 'Escape':
        if (notesDrawer && notesDrawer.classList.contains('open')) {
          notesDrawer.classList.remove('open');
        }
        break;
    }
  });
  
  // Initialize
  updateSlideState(true);
});
