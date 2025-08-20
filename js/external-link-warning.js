// External Link Warning Script - Version 1.1 (CDN Compatible)
document.addEventListener("DOMContentLoaded", function () {
    let currentLink = null;
    let countdown = 10;
    let countdownInterval;
  
    // Define internal domains (your actual site domains, not CDN)
    const internalDomains = [
      'vanburencountymi.gov',
      'www.vanburencountymi.gov',
      'preview.vanburencountymi.gov',
      'eadn-wc02-15234651.nxedge.io', // CDN hostname - needed for CDN compatibility
      // Add any other internal domains you use
    ];
    
    // Function to get the base domain from current URL
    function getBaseDomain() {
      const hostname = window.location.hostname;
      // Remove subdomains like 'preview.' or 'www.'
      const parts = hostname.split('.');
      if (parts.length >= 2) {
        return parts.slice(-2).join('.'); // Get last two parts (e.g., 'vanburencountymi.gov')
      }
      return hostname;
    }
    
    // Add the base domain to internal domains if not already present
    const baseDomain = getBaseDomain();
    if (!internalDomains.includes(baseDomain)) {
      internalDomains.push(baseDomain);
    }
  
    const modal = document.getElementById("exit-modal");
    const countdownEl = document.getElementById("countdown");
    const continueBtn = document.getElementById("continue-btn");
    const cancelBtn = document.getElementById("cancel-btn");
    const realLink = document.getElementById("real-external-link");
  
    // Function to check if a link is truly external
    function isExternalLink(link) {
      const linkHostname = link.hostname;
      const currentHostname = window.location.hostname;
      
      // If the link hostname matches the current hostname, it's internal
      if (linkHostname === currentHostname) {
        return false;
      }
      
      // If the link hostname is in our internal domains list, it's internal
      if (internalDomains.includes(linkHostname)) {
        return false;
      }
      
      // Check if the link is to the same base domain (handles subdomain changes)
      const linkBaseDomain = linkHostname.split('.').slice(-2).join('.');
      if (linkBaseDomain === baseDomain) {
        return false;
      }
      
      // Otherwise, it's external
      return true;
    }
  
    document.querySelectorAll('a[href^="http"]').forEach(link => {
      if (isExternalLink(link)) {
        link.addEventListener("click", function (e) {
          e.preventDefault();
          currentLink = this.href;
          countdown = 10;
          countdownEl.textContent = countdown;
          modal.classList.remove("hidden");
  
          // Start countdown
          countdownInterval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            if (countdown <= 0) {
              clearInterval(countdownInterval);
              // simulate click on the hidden anchor
              if (currentLink) {
                realLink.href = currentLink;
                realLink.click();
              }
              // close the modal
              closeModal();
            }
          }, 1000);
        });
      }
    });
  
    function closeModal() {
      modal.classList.add("hidden");
      clearInterval(countdownInterval);
      currentLink = null;
    }
  
    continueBtn.addEventListener("click", function () {
      if (currentLink) {
        realLink.href = currentLink;
        realLink.click();
      }
      closeModal();
    });
  
    cancelBtn.addEventListener("click", function () {
      closeModal();
    });
  });
  