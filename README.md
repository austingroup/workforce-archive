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
- **Post Display**: View text posts, images, locations, hashtags, likes, and comments
- **SQLite Database**: Fast local database for querying archived data

## Project Structure

```
app/
├── config/
│   └── config.php          # Configuration settings
├── database/
│   ├── schema.sql          # Database schema
│   └── workforce.db        # SQLite database (created after import)
├── metadata/               # JSON export files from legacy system
├── public/                 # Web root
│   ├── assets/
│   │   └── css/
│   │       └── style.css
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── index.php
│   ├── channels.php
│   ├── channel.php
│   └── search.php
├── src/                    # PHP classes
│   ├── Database.php
│   ├── Channel.php
│   └── Post.php
└── import.php              # Data import script
```

## Setup

### Requirements
- PHP 7.4 or higher
- PHP SQLite3 extension
- Web server (Apache, Nginx, or PHP built-in server)

### Installation

1. **Import the data**:
   ```bash
   cd app
   php import.php
   ```
   This will create the SQLite database and import all data from the metadata JSON files.

2. **Start the web server**:
   
   Using PHP built-in server:
   ```bash
   cd public
   php -S localhost:8000
   ```
   
   Or configure your web server to serve from the `public` directory.

3. **Access the application**:
   Open your browser to `http://localhost:8000`

## Configuration

Edit `config/config.php` to customize:
- Database path
- Share drive location (placeholder for future file access)
- Date formats
- Pagination settings

## Usage

### Channels Page
- View all available channels
- Click on a channel to see its posts
- Sidebar shows channel list for quick navigation

### Channel View
- Displays all posts in the selected channel
- Shows images, text content, locations, hashtags
- Displays likes and comment counts

### Search Page
- Filter posts by multiple criteria
- Combine filters for precise results
- Results show which channel each post belongs to

## Data Structure

The application imports the following data:
- **Users**: User profiles and statistics
- **Channels**: Communication channels (both open and DM)
- **Posts**: Text posts, images, comments
- **Places**: Location data for posts
- **Hashtags**: Post hashtags
- **Likes**: Post engagement data

## Notes

- Image files are referenced by ID but not displayed (share drive integration pending)
- DM channels are excluded from the main channel list
- Deleted posts are filtered out from all views
- The sidebar updates dynamically based on available channels

## Future Enhancements

- [ ] Integrate share drive for actual image display
- [ ] Add user profiles page
- [ ] Export search results
- [ ] Advanced analytics dashboard
- [ ] Image thumbnails and galleries
