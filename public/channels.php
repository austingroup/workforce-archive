<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Channel.php';
require_once __DIR__ . '/../src/Post.php';

$activePage = 'channels';
$pageTitle = 'Channels';

$channelModel = new Channel();
$channels = $channelModel->getAllChannels(true, true);

include 'includes/header.php';
?>

<div class="posts-feed">
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 15px; font-size: 18px;">All Channels</h3>
        <p style="color: #666; margin-bottom: 20px;">Select a channel from the sidebar to view posts</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($channels as $channel): ?>
                <?php if ($channel['type'] !== 'dm' && !$channel['is_hidden']): ?>
                <a href="/channel.php?id=<?= urlencode($channel['id']) ?>" 
                   style="display: block; padding: 20px; background: #f9f9f9; border-radius: 6px; text-decoration: none; color: inherit; border: 1px solid #e0e0e0; transition: all 0.2s;"
                   onmouseover="this.style.borderColor='#3a8f71'; this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.borderColor='#e0e0e0'; this.style.transform='translateY(0)'">
                    <div style="font-weight: 600; margin-bottom: 8px; color: #333;">
                        # <?= htmlspecialchars($channel['name']) ?>
                    </div>
                    <div style="font-size: 13px; color: #999;">
                        <?= $channel['post_count'] ?> posts
                    </div>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
