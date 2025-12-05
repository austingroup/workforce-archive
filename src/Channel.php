<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Channel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllChannels($excludeHidden = true, $excludeDMs = true, $excludeEmpty = true) {
        $sql = "SELECT * FROM channels WHERE 1=1";
        $params = [];
        
        if ($excludeHidden) {
            $sql .= " AND is_hidden = 0";
        }
        
        if ($excludeDMs) {
            $sql .= " AND type != 'dm'";
        }
        
        if ($excludeEmpty) {
            $sql .= " AND post_count > 0";
        }
        
        $sql .= " AND is_deleted = 0 ORDER BY name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getChannelById($channelId) {
        $sql = "SELECT * FROM channels WHERE id = ? AND is_deleted = 0";
        return $this->db->fetchOne($sql, [$channelId]);
    }
    
    public function getChannelPostCount($channelId) {
        $sql = "SELECT COUNT(*) as count FROM posts 
                WHERE channel_id = ? AND is_deleted = 0 AND post_type = 'feedItem'";
        $result = $this->db->fetchOne($sql, [$channelId]);
        return $result['count'];
    }
}
