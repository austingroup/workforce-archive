            </div>
        </main>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button class="modal-close" aria-label="Close">&times;</button>
            <button class="modal-prev" aria-label="Previous image">&lsaquo;</button>
            <button class="modal-next" aria-label="Next image">&rsaquo;</button>
            <div class="modal-image-container">
                <img id="modalImage" src="" alt="Full size image">
            </div>
            <div class="modal-footer">
                <div class="modal-counter">
                    <span id="modalCounter"></span>
                </div>
                <a id="modalDownload" href="" download class="modal-download" aria-label="Download image">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/image-modal.js"></script>
</body>
</html>
