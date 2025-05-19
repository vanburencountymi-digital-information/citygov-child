document.addEventListener("DOMContentLoaded", function () {
    const embedContainer = document.getElementById("pdf-embed");
    const loadingMessage = document.getElementById("pdf-loading");
  
    if (embedContainer) {
      const observer = new MutationObserver(() => {
        if (embedContainer.innerHTML.trim() !== "") {
          loadingMessage.style.display = "none";
        }
      });
  
      observer.observe(embedContainer, { childList: true, subtree: true });
    } else {
      loadingMessage.style.display = "none";
    }
  });
  