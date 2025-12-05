<?php
/**
 * Image Configuration
 * Configure the Windows network share details here
 */

return [
    // Windows UNC path to the image share
    'image_server' => '\\\\gazman.com.au\\AustinGroup\\Archive\\Company\\GAZMAN\\VM\\Workforce\\gazman-exported-gcs-files',
    
    // Base path for images (will be combined with image type and post ID)
    // Path structure: {image_server}\image\post\{post_id}\{filename}
    'image_base_path' => 'image\\post',
    
    // Placeholder image (relative to public directory)
    'placeholder_image' => '/assets/images/workforce.jpeg',
    
    // Enable caching (recommended for production)
    'cache_enabled' => true,
    'cache_duration' => 31536000, // 1 year in seconds
];
