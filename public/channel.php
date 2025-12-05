<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Channel.php';
require_once __DIR__ . '/../src/Post.php';

$channelId = $_GET['id'] ?? null;

if (!$channelId) {
    header('Location: /channels.php');
    exit;
}

$channelModel = new Channel();
$postModel = new Post();

$channelInfo = $channelModel->getChannelById($channelId);
if (!$channelInfo) {
    header('Location: /channels.php');
    exit;
}

$activePage = 'channels';
$currentChannelId = $channelId;
$pageTitle = $channelInfo['name'];

// Get all channels for sidebar
$channels = $channelModel->getAllChannels(true, true);

// Handle search within channel
$searchQuery = $_GET['q'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get posts for this channel
$posts = $postModel->getPostsByChannel($channelId, $limit, $offset, $searchQuery);

// If AJAX request, return JSON
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    $html = '';
    foreach ($posts as $post) {
        ob_start();
        include 'includes/post-card.php';
        $html .= ob_get_clean();
    }
    
    echo json_encode([
        'html' => $html,
        'hasMore' => count($posts) === $limit,
        'page' => $page,
        'count' => count($posts)
    ]);
    exit;
}

include 'includes/header.php';
?>

<!-- Channel Search -->
<div class="global-search-box" style="background: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form method="GET" action="/channel.php" style="display: flex; gap: 10px; align-items: center;">
        <input type="hidden" name="id" value="<?= htmlspecialchars($channelId) ?>">
        <input type="text" 
               name="q" 
               placeholder="Search posts (content, users, locations, hashtags...)" 
               value="<?= htmlspecialchars($searchQuery) ?>"
               style="flex: 1; padding: 10px 15px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 14px;">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($searchQuery)): ?>
            <a href="/channel.php?id=<?= urlencode($channelId) ?>" class="btn btn-secondary" style="text-decoration: none;">Clear</a>
        <?php endif; ?>
    </form>
    <?php if (!empty($searchQuery)): ?>
        <div style="margin-top: 10px; font-size: 13px; color: #666;">
            Found <?= count($posts) ?> post<?= count($posts) !== 1 ? 's' : '' ?> matching "<?= htmlspecialchars($searchQuery) ?>"
        </div>
    <?php endif; ?>
</div>

<div class="posts-feed" id="posts-feed">
    <?php if (empty($posts) && !empty($searchQuery)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <p>No posts found matching your search</p>
        </div>
    <?php elseif (empty($posts)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>No posts in this channel yet</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php include 'includes/post-card.php'; ?>
        <?php endforeach; ?>
        
        <div id="scroll-indicator" class="scroll-indicator">
            ↓ Scroll down to load more posts
        </div>
    <?php endif; ?>
</div>

<div id="loading-indicator" class="loading" style="display: none;">
    Loading more posts...
</div>

<script src="/assets/js/infinite-scroll.js"></script>

<?php include 'includes/footer.php'; ?>
