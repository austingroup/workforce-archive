-- Workforce Experience Database Schema

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id TEXT PRIMARY KEY,
    username TEXT NOT NULL,
    first_name TEXT,
    last_name TEXT,
    email TEXT,
    avatar_id TEXT,
    role TEXT,
    state TEXT,
    created_at TEXT,
    last_active_at TEXT,
    post_count INTEGER DEFAULT 0,
    like_count INTEGER DEFAULT 0,
    comment_count INTEGER DEFAULT 0
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_name ON users(first_name, last_name);

-- Channels table
CREATE TABLE IF NOT EXISTS channels (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    slug TEXT,
    type TEXT NOT NULL, -- 'open', 'dm', etc.
    community_id TEXT,
    user_count INTEGER DEFAULT 0,
    post_count INTEGER DEFAULT 0,
    is_hidden BOOLEAN DEFAULT 0,
    is_deleted BOOLEAN DEFAULT 0,
    created_at TEXT,
    updated_at TEXT
);

CREATE INDEX idx_channels_type ON channels(type);
CREATE INDEX idx_channels_name ON channels(name);

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id TEXT PRIMARY KEY,
    content TEXT,
    user_id TEXT,
    channel_id TEXT,
    type TEXT, -- 'image', 'text', 'video', 'comment', etc.
    post_type TEXT, -- 'feedItem', 'comment', 'containerItem'
    parent_post_id TEXT,
    owner_post_id TEXT,
    place_id TEXT,
    is_deleted BOOLEAN DEFAULT 0,
    is_hidden BOOLEAN DEFAULT 0,
    like_count INTEGER DEFAULT 0,
    comment_count INTEGER DEFAULT 0,
    container_item_count INTEGER DEFAULT 0,
    created_at TEXT,
    updated_at TEXT,
    ready_at TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (channel_id) REFERENCES channels(id),
    FOREIGN KEY (place_id) REFERENCES places(id)
);

CREATE INDEX idx_posts_channel ON posts(channel_id);
CREATE INDEX idx_posts_user ON posts(user_id);
CREATE INDEX idx_posts_created ON posts(created_at DESC);
CREATE INDEX idx_posts_parent ON posts(parent_post_id);
CREATE INDEX idx_posts_type ON posts(type);
CREATE INDEX idx_posts_place ON posts(place_id);

-- Post files (images, attachments)
CREATE TABLE IF NOT EXISTS post_files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id TEXT NOT NULL,
    file_id TEXT NOT NULL,
    folder_id TEXT,
    file_name TEXT,
    file_order INTEGER DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE INDEX idx_post_files_post ON post_files(post_id);

-- Hashtags
CREATE TABLE IF NOT EXISTS hashtags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id TEXT NOT NULL,
    hashtag TEXT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE INDEX idx_hashtags_post ON hashtags(post_id);
CREATE INDEX idx_hashtags_tag ON hashtags(hashtag);

-- Mentions
CREATE TABLE IF NOT EXISTS mentions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id TEXT NOT NULL,
    user_id TEXT,
    mention_text TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_mentions_post ON mentions(post_id);
CREATE INDEX idx_mentions_user ON mentions(user_id);

-- Places/Locations
CREATE TABLE IF NOT EXISTS places (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    address TEXT,
    latitude REAL,
    longitude REAL,
    created_at TEXT
);

CREATE INDEX idx_places_name ON places(name);

-- Likes
CREATE TABLE IF NOT EXISTS likes (
    id TEXT PRIMARY KEY,
    post_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    created_at TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_likes_post ON likes(post_id);
CREATE INDEX idx_likes_user ON likes(user_id);

-- Channel members (for tracking who can see what)
CREATE TABLE IF NOT EXISTS channel_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    FOREIGN KEY (channel_id) REFERENCES channels(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE(channel_id, user_id)
);

CREATE INDEX idx_channel_members_channel ON channel_members(channel_id);
CREATE INDEX idx_channel_members_user ON channel_members(user_id);
