document.addEventListener("DOMContentLoaded", function () {
    const offsetHeadings = document.querySelectorAll("h1, h2, h3, h4");
  
    offsetHeadings.forEach((heading) => {
      if (!heading.classList.contains("anchor-offset")) {
        heading.classList.add("anchor-offset");
      }
    });
  });
  