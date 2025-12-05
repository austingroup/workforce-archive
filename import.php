<?php
/**
 * Import script to load metadata JSON files into SQLite database
 * Run: php import.php
 */

// Increase memory limit for large imports
ini_set('memory_limit', '512M');

require_once __DIR__ . '/config/config.php';

$metadataPath = __DIR__ . '/metadata/';

echo "Starting import...\n";

// Create database connection
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Create tables from schema
echo "Creating database schema...\n";
$schema = file_get_contents(__DIR__ . '/database/schema.sql');
try {
    $db->exec($schema);
    echo "Schema created successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Schema already exists, skipping creation.\n";
    } else {
        throw $e;
    }
}

// Import Users
echo "\nImporting users...\n";
$usersFile = $metadataPath . 'users_export_0.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    $stmt = $db->prepare("INSERT OR REPLACE INTO users 
        (id, username, first_name, last_name, email, avatar_id, role, state, created_at, last_active_at, post_count, like_count, comment_count) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($users as $user) {
        $email = !empty($user['emails']) ? $user['emails'][0] : null;
        $stmt->execute([
            $user['_id'],
            $user['username'] ?? '',
            $user['firstName'] ?? '',
            $user['lastName'] ?? '',
            $email,
            $user['avatarId'] ?? null,
            $user['role_'] ?? null,
            $user['state'] ?? '',
            $user['createdAt'] ?? null,
            $user['lastActiveAt'] ?? null,
            $user['postCount'] ?? 0,
            $user['likeCount'] ?? 0,
            $user['commentCount'] ?? 0
        ]);
        $count++;
    }
    echo "Imported $count users.\n";
}

// Import Channels
echo "\nImporting channels...\n";
$channelsFile = $metadataPath . 'channels_export_0.json';
if (file_exists($channelsFile)) {
    $channels = json_decode(file_get_contents($channelsFile), true);
    $stmt = $db->prepare("INSERT OR REPLACE INTO channels 
        (id, name, slug, type, community_id, user_count, post_count, is_hidden, is_deleted, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($channels as $channel) {
        $stmt->execute([
            $channel['_id'],
            $channel['name'] ?? '',
            $channel['slug'] ?? '',
            $channel['type'] ?? 'open',
            $channel['communityId'] ?? null,
            $channel['userCount'] ?? 0,
            $channel['postCount'] ?? 0,
            isset($channel['isHidden']) ? ($channel['isHidden'] ? 1 : 0) : 0,
            isset($channel['isDeleted']) ? ($channel['isDeleted'] ? 1 : 0) : 0,
            $channel['createdAt'] ?? null,
            $channel['updatedAt'] ?? null
        ]);
        $count++;
    }
    echo "Imported $count channels.\n";
}

// Import Places
echo "\nImporting places...\n";
$placesFile = $metadataPath . 'places_export_0.json';
if (file_exists($placesFile)) {
    $places = json_decode(file_get_contents($placesFile), true);
    $stmt = $db->prepare("INSERT OR REPLACE INTO places 
        (id, name, address, latitude, longitude, created_at) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($places as $place) {
        $stmt->execute([
            $place['_id'],
            $place['name'] ?? '',
            $place['address'] ?? null,
            $place['latitude'] ?? null,
            $place['longitude'] ?? null,
            $place['createdAt'] ?? null
        ]);
        $count++;
    }
    echo "Imported $count places.\n";
}

// Import Posts (all post export files)
echo "\nImporting posts...\n";
$postFiles = glob($metadataPath . 'posts_export_*.json');
$totalPosts = 0;

$postStmt = $db->prepare("INSERT OR REPLACE INTO posts 
    (id, content, user_id, channel_id, type, post_type, parent_post_id, owner_post_id, place_id, 
     is_deleted, is_hidden, like_count, comment_count, container_item_count, created_at, updated_at, ready_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$fileStmt = $db->prepare("INSERT INTO post_files (post_id, file_id, file_order) VALUES (?, ?, ?)");
$hashtagStmt = $db->prepare("INSERT INTO hashtags (post_id, hashtag) VALUES (?, ?)");

// Start transaction for better performance
$db->beginTransaction();
$batchCount = 0;

foreach ($postFiles as $postFile) {
    echo "Processing " . basename($postFile) . "...\n";
    
    // Stream the file instead of loading it all at once
    $content = file_get_contents($postFile);
    $posts = json_decode($content, true);
    unset($content); // Free memory immediately
    
    if (!$posts) {
        echo "Skipping empty file.\n";
        continue;
    }
    
    foreach ($posts as $post) {
        // Insert post
        $postStmt->execute([
            $post['_id'],
            $post['content'] ?? '',
            $post['userId'] ?? null,
            $post['channelId'] ?? null,
            $post['type'] ?? null,
            $post['postType'] ?? null,
            $post['parentPostId'] ?? null,
            $post['ownerPostId'] ?? null,
            $post['placeId'] ?? null,
            isset($post['isDeleted']) ? ($post['isDeleted'] ? 1 : 0) : 0,
            isset($post['isHidden']) ? ($post['isHidden'] ? 1 : 0) : 0,
            $post['likeCount'] ?? 0,
            $post['commentCount'] ?? 0,
            $post['containerItemCount'] ?? 0,
            $post['createdAt'] ?? null,
            $post['updatedAt'] ?? null,
            $post['readyAt'] ?? null
        ]);
        
        // Insert post files from parent post
        if (!empty($post['fileIds'])) {
            foreach ($post['fileIds'] as $index => $fileId) {
                $fileStmt->execute([$post['_id'], $fileId, $index]);
            }
        }
        
        // If this is a container post, find and import files from containerItem children
        if (($post['type'] ?? null) === 'container' && ($post['containerItemCount'] ?? 0) > 0) {
            $fileOffset = count($post['fileIds'] ?? []);
            foreach ($posts as $childPost) {
                if (($childPost['parentPostId'] ?? null) === $post['_id'] && 
                    ($childPost['postType'] ?? null) === 'containerItem') {
                    if (!empty($childPost['fileIds'])) {
                        foreach ($childPost['fileIds'] as $fileId) {
                            $fileStmt->execute([$post['_id'], $fileId, $fileOffset++]);
                        }
                    }
                }
            }
        }
        
        // Insert hashtags
        if (!empty($post['hashtags'])) {
            foreach ($post['hashtags'] as $hashtag) {
                // Handle both string and object formats
                $tagValue = is_array($hashtag) ? ($hashtag['tag'] ?? '') : $hashtag;
                if ($tagValue) {
                    $hashtagStmt->execute([$post['_id'], $tagValue]);
                }
            }
        }
        
        $totalPosts++;
        $batchCount++;
        
        // Commit transaction every 1000 posts to free memory
        if ($batchCount >= 1000) {
            $db->commit();
            $db->beginTransaction();
            $batchCount = 0;
            echo "  ... processed $totalPosts posts so far\n";
        }
    }
    
    // Free memory after each file
    unset($posts);
    gc_collect_cycles();
}

// Commit remaining posts
$db->commit();
echo "Imported $totalPosts posts.\n";

// Import Likes
echo "\nImporting likes...\n";
$likesFile = $metadataPath . 'likes_export_0.json';
if (file_exists($likesFile)) {
    $likes = json_decode(file_get_contents($likesFile), true);
    $stmt = $db->prepare("INSERT OR REPLACE INTO likes (id, post_id, user_id, created_at) VALUES (?, ?, ?, ?)");
    
    $count = 0;
    foreach ($likes as $like) {
        $stmt->execute([
            $like['_id'],
            $like['postId'] ?? null,
            $like['userId'] ?? null,
            $like['createdAt'] ?? null
        ]);
        $count++;
    }
    echo "Imported $count likes.\n";
}

echo "\n✅ Import completed successfully!\n";
echo "Database created at: " . DB_PATH . "\n";
