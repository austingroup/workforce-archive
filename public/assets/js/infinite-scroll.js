// Infinite scroll pagination
let isLoading = false;
let hasMore = true;
let currentPage = 1;
let scrollContainer = null;

function loadMorePosts() {
    if (isLoading || !hasMore) {
        return;
    }
    
    isLoading = true;
    const loader = document.getElementById('loading-indicator');
    if (loader) {
        loader.style.display = 'block';
    }
    
    const url = new URL(window.location.href);
    url.searchParams.set('page', currentPage + 1);
    url.searchParams.set('ajax', '1');
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            if (!text || text.trim().length === 0) {
                throw new Error('Empty response');
            }
            
            const data = JSON.parse(text);
            
            if (data.html && data.html.length > 0) {
                const feed = document.getElementById('posts-feed');
                if (!feed) {
                    return;
                }
                
                feed.insertAdjacentHTML('beforeend', data.html);
                currentPage++;
                hasMore = data.hasMore;
                
                // Re-attach image modal handlers to new posts
                if (typeof window.attachImageModalHandlers === 'function') {
                    window.attachImageModalHandlers();
                }
                
                const scrollIndicator = document.getElementById('scroll-indicator');
                if (scrollIndicator) {
                    scrollIndicator.style.display = hasMore ? 'block' : 'none';
                }
            } else {
                hasMore = false;
                
                const scrollIndicator = document.getElementById('scroll-indicator');
                if (scrollIndicator) {
                    scrollIndicator.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading posts:', error);
            hasMore = false;
        })
        .finally(() => {
            isLoading = false;
            if (loader) {
                loader.style.display = 'none';
            }
        });
}

// Detect scroll near bottom of the scrollable container
function checkScroll() {
    if (!scrollContainer) return;
    
    const scrollTop = scrollContainer.scrollTop;
    const scrollHeight = scrollContainer.scrollHeight;
    const clientHeight = scrollContainer.clientHeight;
    const distanceFromBottom = scrollHeight - (scrollTop + clientHeight);
    
    if (distanceFromBottom <= 800) {
        loadMorePosts();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    scrollContainer = document.querySelector('.content-area');
    
    if (!scrollContainer) {
        return;
    }
    
    let scrollTimeout;
    scrollContainer.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(checkScroll, 100);
    }, { passive: true });
});
