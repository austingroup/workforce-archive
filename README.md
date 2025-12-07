# Workforce Experience Archive

A PHP web application for viewing archived data from the legacy Workforce Experience retail tracking system.

## Features

- **Channels View**: Browse all channels and view posts within each channel
- **Search**: Advanced search with filters for:
  - Date range
  - Location/Place
  - Hashtags
  - Users
  - Channels
  - Post IDs (wildcards supported)
- **Post Display**: View text posts, images, locations, hashtags, likes, and comments
- **Infinite Scroll**: Pagination with automatic loading (20 posts at a time)
- **Image Gallery**: Full-screen modal viewer with keyboard navigation
- **Image Proxy**: Serves images from Windows network share
- **Download Images**: Download images with custom filenames (username-postid-imagenum.jpg)
- **SQLite Database**: Fast local database for querying archived data

## Project Structure

```
app/
├── config/
│   ├── config.php          # Configuration settings
│   └── image-config.php    # Image server configuration
├── database/
│   ├── schema.sql          # Database schema
│   └── workforce.db        # SQLite database (created after import)
├── metadata/               # JSON export files from legacy system
├── public/                 # Web root
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   ├── js/
│   │   │   ├── infinite-scroll.js
│   │   │   └── image-modal.js
│   │   └── images/
│   │       └── workforce.jpeg
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── post-card.php
│   ├── index.php
│   ├── channels.php
│   ├── channel.php
│   ├── search.php
│   └── image-proxy.php     # Image proxy for network share
├── src/                    # PHP classes
│   ├── Database.php
│   ├── Channel.php
│   └── Post.php
└── import.php              # Data import script
```

## Setup

### Requirements
- PHP 7.4 or higher (PHP 8.4+ recommended)
- PHP SQLite3 extension
- Web server (Apache, Nginx, IIS, or PHP built-in server)
- For Windows Server deployment:
  - IIS configured with PHP
  - Network share access configured for application pool identity
  - Domain account or computer account with read access to image archive

### Installation

1. **Place metadata files**:
   - Copy all JSON export files to the `metadata/` directory
   - Required files: users_export_0.json, channels_export_0.json, posts_export_*.json, user_files_*_export_*.json, etc.

2. **Configure image server** (for Windows deployment):
   Edit `config/image-config.php`:
   ```php
   return [
       'image_server' => '\\\\server\\share\\path',
       'image_base_path' => 'image\\post',
       'placeholder_image' => '/assets/images/workforce.jpeg',
       'cache_enabled' => true,
       'cache_duration' => 31536000,
   ];
   ```

3. **Import the data**:
   ```bash
   cd app
   php import.php
   ```
   This will:
   - Create the SQLite database
   - Build file lookup index from user_files exports (~95,000 files)
   - Import users, channels, places, posts (container posts with child images), hashtags, and likes
   - Process takes several minutes depending on data size

4. **Configure web server**:
   
   **For IIS (Windows Server)**:
   - Configure application pool to run as domain account with network share access
   - Set document root to `public` directory
   - Ensure URL rewriting is disabled (uses query strings)
   
   **For PHP built-in server** (development):
   ```bash
   cd public
   php -S localhost:8000
   ```
   
   **For Apache/Nginx**:
   - Configure document root to `public` directory
   - Ensure PHP is configured to handle .php files

5. **Access the application**:
   Open your browser to `http://localhost:8000` or your configured domain

## Configuration

### Database Configuration
Edit `config/config.php` to customize:
- Database path
- Date formats
- Pagination settings

### Image Server Configuration
Edit `config/image-config.php` for Windows network share:
- `image_server`: UNC path to the base archive location
- `image_base_path`: Subdirectory structure (e.g., `image\post`)
- `placeholder_image`: Path to placeholder image for missing files
- `cache_enabled`: Enable/disable browser caching
- `cache_duration`: Cache duration in seconds

### IIS Application Pool Configuration
For production deployment on Windows Server:

1. **Set Application Pool Identity**:
   - IIS Manager → Application Pools → [your pool]
   - Advanced Settings → Identity
   - Set to domain account with network share access
   - Example: `DOMAIN\serviceaccount`

2. **Grant Network Share Permissions**:
   - On file server, grant read access to the application pool identity
   - Or grant access to the computer account: `DOMAIN\SERVERNAME$`

## Usage

### Home Page
- Dashboard showing recent activity
- Quick navigation to channels and search

### Channels Page
- View all available channels (DM channels excluded)
- Click on a channel to see its posts
- Sidebar shows channel list for quick navigation
- Posts load with infinite scroll (20 at a time)

### Channel View
- Displays all posts in the selected channel
- Shows images (up to 6 visible, more accessible in modal), text content, locations, hashtags
- Displays likes and comment counts
- Search within channel (supports wildcards in spaces)
- Click any image to open full-screen modal viewer
- Infinite scroll for loading more posts

### Image Modal
- Full-screen image viewer
- Navigate with arrow keys or on-screen buttons
- Shows image counter (e.g., "1 / 12")
- Download button with custom filename format
- Click outside or press Escape to close

### Search Page
- Filter posts by multiple criteria
- Combine filters for precise results
- Search by post ID (exact or wildcard)
- Wildcard search: spaces convert to wildcards (e.g., "john smith" matches "johnathan smith")
- Results show which channel each post belongs to
- Infinite scroll for results

## Data Structure

The application imports the following data:
- **Users**: User profiles and statistics (58 users)
- **Channels**: Communication channels - open channels only, DMs excluded (188 channels)
- **Posts**: Text posts, container posts with multiple images (~105,000 posts)
  - Container posts: Parent posts with multiple child image posts
  - Each child image has its own file mapping
- **Places**: Location data for posts (141 locations)
- **Hashtags**: Post hashtags for categorization
- **Likes**: Post engagement data
- **Post Files**: File mappings with folder IDs and filenames (~95,000 file references)
  - Maps file IDs to actual folder locations on network share
  - Extracted from user_files exports during import

### Image Architecture
Images are served from a Windows network share:
- Path structure: `\\server\share\image\post\{folder_id}\{filename}`
- `folder_id`: UUID extracted from original GCS path
- `filename`: Original filename (e.g., `ios.jpeg`)
- Served through `image-proxy.php` with caching
- Placeholder image shown if file not accessible

## Troubleshooting

### Images Not Loading
1. **Check network share permissions**:
   - Verify IIS application pool identity has read access
   - Test access from command line: `dir \\server\share\path`

2. **Verify configuration**:
   - Check `config/image-config.php` paths are correct
   - Ensure backslashes are properly escaped: `\\\\server\\share`

3. **Check application pool**:
   - IIS Manager → Application Pools
   - Verify identity is domain account or has network access
   - Restart application pool after changes

### Import Issues
1. **Memory errors**: Increase `memory_limit` in import.php (default: 1024M)
2. **Slow import**: Normal for large datasets; takes 5-10 minutes
3. **Missing files**: Ensure all user_files_*_export_*.json files are present

### Search Not Working
1. Verify SQLite database was created successfully
2. Check that posts were imported (check post count in database)
3. Ensure spaces in search convert to wildcards (automatic)

## Notes

- Images are served from Windows network share via image proxy
- DM channels are excluded from the main channel list
- Deleted posts are filtered out from all views
- The sidebar updates dynamically based on available channels
- Container posts automatically aggregate images from child posts
- Image modal supports keyboard navigation (arrows, escape)
- Download filenames follow format: `username-postid-imagenum.jpg`
- Search uses wildcard matching for flexible queries
- Browser caching enabled for images (1 year default)

## Technical Details

### Database Schema
- SQLite database with indexed foreign keys
- Optimized for read-heavy workloads
- Full-text search capabilities on content and metadata

### Performance Optimizations
- Infinite scroll prevents loading entire dataset
- Image caching reduces server load
- Batch transaction commits during import
- Memory management with garbage collection
- Indexed database queries for fast search

### Security Considerations
- Read-only archive (no write operations)
- Network share accessed via service account
- Placeholder images for inaccessible files
- Input sanitization on all user queries
- SQL injection prevention via prepared statements

## Future Enhancements

- [ ] User profiles page with activity history
- [ ] Export search results to CSV/JSON
- [ ] Advanced analytics dashboard
- [ ] Image thumbnail generation for faster loading
- [ ] Document viewing support (PDFs from documents_export)
- [ ] Comments display in post modal
- [ ] Share/permalink functionality for specific posts
- [ ] Mobile-responsive design improvements
