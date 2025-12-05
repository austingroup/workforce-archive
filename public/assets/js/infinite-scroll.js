// Infinite scroll pagination
console.log('🚀 Infinite scroll initialized');

let isLoading = false;
let hasMore = true;
let currentPage = 1;

function loadMorePosts() {
    console.log('📊 loadMorePosts called - isLoading:', isLoading, 'hasMore:', hasMore, 'currentPage:', currentPage);
    
    if (isLoading) {
        console.log('⏸️ Already loading, skipping...');
        return;
    }
    
    if (!hasMore) {
        console.log('🛑 No more posts available');
        return;
    }
    
    isLoading = true;
    const loader = document.getElementById('loading-indicator');
    if (loader) {
        loader.style.display = 'block';
        console.log('⏳ Showing loading indicator');
    }
    
    const url = new URL(window.location.href);
    url.searchParams.set('page', currentPage + 1);
    url.searchParams.set('ajax', '1');
    
    console.log('🌐 Fetching URL:', url.toString());
    
    fetch(url)
        .then(response => {
            console.log('📥 Response status:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('📄 Raw response length:', text.length);
            console.log('📄 First 200 chars:', text.substring(0, 200));
            
            if (!text || text.trim().length === 0) {
                throw new Error('Empty response');
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('❌ JSON parse error:', e);
                console.error('Response text:', text);
                throw e;
            }
            
            console.log('✅ Parsed JSON data:', data);
            console.log('HTML length:', data.html?.length || 0);
            console.log('Has more:', data.hasMore);
            console.log('Count:', data.count);
            
            if (data.html && data.html.length > 0) {
                const feed = document.getElementById('posts-feed');
                if (!feed) {
                    console.error('❌ posts-feed element not found!');
                    return;
                }
                
                console.log('➕ Appending HTML to feed');
                feed.insertAdjacentHTML('beforeend', data.html);
                currentPage++;
                hasMore = data.hasMore;
                console.log('✅ Successfully loaded page', currentPage, '- hasMore:', hasMore);
            } else {
                hasMore = false;
                console.log('🏁 No more posts to load');
            }
        })
        .catch(error => {
            console.error('❌ Error loading posts:', error);
            hasMore = false;
        })
        .finally(() => {
            isLoading = false;
            if (loader) {
                loader.style.display = 'none';
                console.log('✅ Hidden loading indicator');
            }
        });
}

// Detect scroll near bottom
function checkScroll() {
    const scrollPosition = window.innerHeight + window.scrollY;
    const pageHeight = document.documentElement.scrollHeight;
    const distanceFromBottom = pageHeight - scrollPosition;
    
    console.log('📜 Scroll check - scrollY:', Math.round(window.scrollY), 'distance from bottom:', Math.round(distanceFromBottom) + 'px');
    
    if (distanceFromBottom <= 1000) {
        console.log('🎯 Within 1000px of bottom, calling loadMorePosts');
        loadMorePosts();
    } else {
        console.log('⏸️ Still ' + Math.round(distanceFromBottom) + 'px from bottom');
    }
}

// Throttle scroll events
let scrollTimeout;
window.addEventListener('scroll', () => {
    console.log('👂 Scroll event detected at', window.scrollY);
    if (scrollTimeout) clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(checkScroll, 100);
}, { passive: true });

// Log when page is ready
console.log('✅ Scroll listener attached');
console.log('📦 Initial page height:', document.documentElement.scrollHeight);
console.log('📏 Window height:', window.innerHeight);
console.log('📊 Can scroll:', document.documentElement.scrollHeight > window.innerHeight);
console.log('⏳ Ready - scroll down to load more posts');
