/**
 * Image Modal - Click to view full size images with navigation
 */

let currentImages = [];
let currentIndex = 0;
let currentPostData = null;
let modal, modalImage, modalCounter, closeBtn, prevBtn, nextBtn, overlay;

function openModal() {
    if (currentImages.length === 0) return;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    showImage(currentIndex);
}

function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function showImage(index) {
    if (index < 0 || index >= currentImages.length) return;
    
    currentIndex = index;
    modalImage.src = currentImages[currentIndex];
    modalCounter.textContent = `${currentIndex + 1} / ${currentImages.length}`;
    
    // Update download link
    const downloadBtn = document.getElementById('modalDownload');
    if (downloadBtn && currentPostData) {
        downloadBtn.href = currentImages[currentIndex];
        const filename = `${currentPostData.username}-${currentPostData.postId}-${currentIndex + 1}.jpg`;
        downloadBtn.download = filename;
    }
    
    // Show/hide navigation buttons
    prevBtn.style.display = currentImages.length > 1 ? 'flex' : 'none';
    nextBtn.style.display = currentImages.length > 1 ? 'flex' : 'none';
}

function showPrevious() {
    if (currentIndex > 0) {
        showImage(currentIndex - 1);
    } else {
        // Loop to last image
        showImage(currentImages.length - 1);
    }
}

function showNext() {
    if (currentIndex < currentImages.length - 1) {
        showImage(currentIndex + 1);
    } else {
        // Loop to first image
        showImage(0);
    }
}

// Attach click handlers to all post images
function attachImageModalHandlers() {
    document.querySelectorAll('.post-images').forEach(postImages => {
        const images = postImages.querySelectorAll('.post-image');
        
        images.forEach((imageDiv, index) => {
            // Remove existing handler to prevent duplicates
            const newImageDiv = imageDiv.cloneNode(true);
            imageDiv.parentNode.replaceChild(newImageDiv, imageDiv);
            
            newImageDiv.addEventListener('click', function() {
                // Get all images for this post
                const postImagesContainer = newImageDiv.closest('.post-images');
                const parentImages = postImagesContainer.querySelectorAll('.post-image');
                
                // Get image sources from data-image-src attribute (for proxy URLs)
                currentImages = Array.from(parentImages).map(div => {
                    return div.dataset.imageSrc || div.querySelector('img')?.src;
                }).filter(src => src);
                
                currentIndex = Array.from(parentImages).indexOf(newImageDiv);
                
                // Get post data for filename
                const postCard = newImageDiv.closest('.post-card');
                const username = postCard?.querySelector('.post-user-name')?.textContent.trim().replace(/\s+/g, '-') || 'user';
                const postId = postImagesContainer?.dataset.postId || 'unknown';
                
                currentPostData = {
                    username: username,
                    postId: postId
                };
                
                openModal();
            });
        });
    });
}

// Make function available globally for infinite scroll
window.attachImageModalHandlers = attachImageModalHandlers;

// Initialize modal functionality
document.addEventListener('DOMContentLoaded', function() {
    modal = document.getElementById('imageModal');
    modalImage = document.getElementById('modalImage');
    modalCounter = document.getElementById('modalCounter');
    closeBtn = document.querySelector('.modal-close');
    prevBtn = document.querySelector('.modal-prev');
    nextBtn = document.querySelector('.modal-next');
    overlay = document.querySelector('.modal-overlay');
    
    // Event listeners
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
    prevBtn.addEventListener('click', showPrevious);
    nextBtn.addEventListener('click', showNext);
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (modal.style.display === 'flex') {
            if (e.key === 'Escape') {
                closeModal();
            } else if (e.key === 'ArrowLeft') {
                showPrevious();
            } else if (e.key === 'ArrowRight') {
                showNext();
            }
        }
    });
    
    // Initial attachment
    attachImageModalHandlers();
});
