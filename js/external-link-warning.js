document.addEventListener("DOMContentLoaded", function () {
    let currentLink = null;
  
    document.querySelectorAll('a[href^="http"]').forEach(link => {
      const host = window.location.hostname;
      const isExternal = link.hostname !== host;
  
      if (isExternal) {
        link.classList.add("external-link");
        link.addEventListener("click", function (e) {
          e.preventDefault();
          currentLink = this.href;
  
          const confirmed = confirm("You are now leaving the Van Buren County website. We are not responsible for external content. Continue?");
          if (confirmed) {
            window.open(currentLink, "_blank");
          }
        });
      }
    });
  });
  