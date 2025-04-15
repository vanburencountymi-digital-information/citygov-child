document.addEventListener("DOMContentLoaded", function () {
    let currentLink = null;
    let countdown = 5;
    let countdownInterval;
  
    const modal = document.getElementById("exit-modal");
    const countdownEl = document.getElementById("countdown");
    const continueBtn = document.getElementById("continue-btn");
    const cancelBtn = document.getElementById("cancel-btn");
  
    document.querySelectorAll('a[href^="http"]').forEach(link => {
      const isExternal = link.hostname !== window.location.hostname;
      if (isExternal) {
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
              window.open(currentLink, '_blank');
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
      if (currentLink) window.open(currentLink, '_blank');
      closeModal();
    });
  
    cancelBtn.addEventListener("click", function () {
      closeModal();
    });
  });
  