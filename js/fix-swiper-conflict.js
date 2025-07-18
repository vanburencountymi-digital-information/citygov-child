(function () {
  // Store the original Swiper before any modifications
  const originalSwiper = window.Swiper;
  
  // Create a proxy to prevent Swiper from being redefined
  let currentSwiper = originalSwiper;
  
  Object.defineProperty(window, 'Swiper', {
    get: function() {
      return currentSwiper;
    },
    set: function(newSwiper) {
      // If this is the first time Swiper is being set, allow it
      if (!window.__swiperInitialized) {
        currentSwiper = newSwiper;
        window.__swiperInitialized = true;
        return;
      }
      
      // If Swiper is already initialized, prevent redefinition
      // This prevents conflicts between Elementor slideshows and The Events Calendar
      return;
    },
    configurable: true
  });

  // Monitor for any attempts to modify Swiper properties
  if (originalSwiper) {
    // Create a proxy for the Swiper constructor to catch any modifications
    const swiperProxy = new Proxy(originalSwiper, {
      get: function(target, prop) {
        return target[prop];
      },
      set: function(target, prop, value) {
        target[prop] = value;
        return true;
      }
    });
    
    currentSwiper = swiperProxy;
  }

  // Monitor for any global Swiper modifications and restore if needed
  let swiperCheckInterval = setInterval(() => {
    if (window.Swiper && window.Swiper !== currentSwiper) {
      // Restore our version
      currentSwiper = window.Swiper;
    }
  }, 1000);

  // Clean up interval after 10 seconds
  setTimeout(() => {
    clearInterval(swiperCheckInterval);
  }, 10000);
})();