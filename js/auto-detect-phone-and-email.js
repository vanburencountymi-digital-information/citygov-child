document.addEventListener("DOMContentLoaded", function() {
    const body = document.body;
  
    // Only run on single posts (not pages)
    if (!body.classList.contains('single-post')) {
      return;
    }
  
    const postContent = document.querySelector('.entry');
  
    if (!postContent) return;
  
    const walkAndReplace = (node) => {
      // Skip <a> tags and their descendants
      if (node.closest && node.closest('a')) {
        return;
      }
  
      if (node.nodeType === Node.TEXT_NODE) {
        const replaced = replaceText(node.nodeValue);
        if (replaced !== node.nodeValue) {
          const span = document.createElement('span');
          span.innerHTML = replaced;
          node.replaceWith(span);
        }
      } else {
        node.childNodes.forEach(walkAndReplace);
      }
    };
  
    const replaceText = (text) => {
      // Email regex
      text = text.replace(
        /([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g,
        '<a href="mailto:$1" class="email-link">$1</a>'
      );
  
      // Phone regex
      text = text.replace(
        /(\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4})/g,
        (match) => {
          const cleaned = match.replace(/\D/g, ''); // remove non-digits
          return `<a href="tel:+1${cleaned}" class="phone-number">${match}</a>`;
        }
      );
  
      return text;
    };
  
    walkAndReplace(postContent);
  });
  