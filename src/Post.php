<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Post {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getPostsByChannel($channelId, $limit = 20, $offset = 0, $searchQuery = '') {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username, u.avatar_id,
                pl.name as place_name, pl.address as place_address
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN places pl ON p.place_id = pl.id
                WHERE p.channel_id = ? 
                AND p.is_deleted = 0 
                AND p.post_type = 'feedItem'";
        
        $params = [$channelId];
        
        // Add search filter if provided
        if (!empty($searchQuery)) {
            $sql .= " AND (p.content LIKE ? 
                      OR p.id LIKE ?
                      OR u.first_name LIKE ? 
                      OR u.last_name LIKE ? 
                      OR u.username LIKE ?
                      OR pl.name LIKE ? 
                      OR pl.address LIKE ?
                      OR p.id IN (SELECT post_id FROM hashtags WHERE hashtag LIKE ?))";
            $searchTerm = '%' . str_replace(' ', '%', $searchQuery) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $posts = $this->db->fetchAll($sql, $params);
        
        // Get files, hashtags for each post
        foreach ($posts as &$post) {
            $post['files'] = $this->getPostFiles($post['id']);
            $post['hashtags'] = $this->getPostHashtags($post['id']);
            $post['comments'] = $this->getPostComments($post['id']);
        }
        
        return $posts;
    }
    
    public function searchPosts($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username, u.avatar_id,
                pl.name as place_name, pl.address as place_address,
                c.name as channel_name
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN places pl ON p.place_id = pl.id
                LEFT JOIN channels c ON p.channel_id = c.id
                WHERE p.is_deleted = 0 AND p.post_type = 'feedItem'";
        
        $params = [];
        
        // Global search query (searches everywhere)
        if (!empty($filters['q'])) {
            $sql .= " AND (p.content LIKE ? 
                      OR p.id LIKE ?
                      OR u.first_name LIKE ? 
                      OR u.last_name LIKE ? 
                      OR u.username LIKE ?
                      OR pl.name LIKE ? 
                      OR pl.address LIKE ?
                      OR c.name LIKE ?
                      OR p.id IN (SELECT post_id FROM hashtags WHERE hashtag LIKE ?))";
            $searchTerm = '%' . str_replace(' ', '%', $filters['q']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Date filters
        if (!empty($filters['start_date'])) {
            $sql .= " AND p.created_at >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND p.created_at <= ?";
            $params[] = $filters['end_date'];
        }
        
        // Location/Place filter
        if (!empty($filters['location'])) {
            $sql .= " AND (pl.name LIKE ? OR pl.address LIKE ?)";
            $params[] = '%' . $filters['location'] . '%';
            $params[] = '%' . $filters['location'] . '%';
        }
        
        // User filter
        if (!empty($filters['user'])) {
            $sql .= " AND (u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $params[] = '%' . $filters['user'] . '%';
            $params[] = '%' . $filters['user'] . '%';
            $params[] = '%' . $filters['user'] . '%';
        }
        
        // Channel filter
        if (!empty($filters['channel'])) {
            $sql .= " AND p.channel_id = ?";
            $params[] = $filters['channel'];
        }
        
        // Hashtag filter
        if (!empty($filters['hashtag'])) {
            $sql .= " AND p.id IN (SELECT post_id FROM hashtags WHERE hashtag LIKE ?)";
            $params[] = '%' . $filters['hashtag'] . '%';
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $posts = $this->db->fetchAll($sql, $params);
        
        // Get files, hashtags for each post
        foreach ($posts as &$post) {
            $post['files'] = $this->getPostFiles($post['id']);
            $post['hashtags'] = $this->getPostHashtags($post['id']);
        }
        
        return $posts;
    }
    
    public function getPostFiles($postId) {
        $sql = "SELECT * FROM post_files WHERE post_id = ? ORDER BY file_order";
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    public function getPostHashtags($postId) {
        $sql = "SELECT hashtag FROM hashtags WHERE post_id = ?";
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    public function getPostComments($postId, $limit = 5) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.parent_post_id = ? AND p.is_deleted = 0 AND p.post_type = 'comment'
                ORDER BY p.created_at ASC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$postId, $limit]);
    }
}
