/**
 * PDF Replacement Modal JavaScript
 * Handles the modal functionality and AJAX file upload for replacing PDF documents
 */

document.addEventListener('DOMContentLoaded', function() {
    const replaceBtn = document.getElementById('replace-pdf-btn');
    const modal = document.getElementById('pdf-replace-modal');
    const closeBtn = document.getElementById('close-pdf-modal');
    const cancelBtn = document.getElementById('cancel-pdf-replace');
    const form = document.getElementById('pdf-replace-form');
    const fileInput = document.getElementById('pdf-file');
    const titleInput = document.getElementById('pdf-title');

    // Open modal
    if (replaceBtn) {
        replaceBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        });
    }

    // Close modal functions
    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
        form.reset();
    }

    // Close modal event listeners
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // File input validation
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    this.value = '';
                    return;
                }

                // Check file type
                if (file.type !== 'application/pdf') {
                    alert('Please select a PDF file');
                    this.value = '';
                    return;
                }

                // Update title if empty
                if (!titleInput.value.trim()) {
                    titleInput.value = file.name.replace('.pdf', '');
                }
            }
        });
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitBtn = form.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = 'Uploading...';
            submitBtn.disabled = true;

            // AJAX request
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('PDF replaced successfully!');
                    closeModal();
                    // Reload the page to show the new PDF
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.data || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the file. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}); 