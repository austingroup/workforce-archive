<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Channel.php';
require_once __DIR__ . '/../src/Post.php';

$activePage = 'search';
$pageTitle = 'Search Posts';

$channelModel = new Channel();
$postModel = new Post();

// Get all channels for sidebar and filter
$channels = $channelModel->getAllChannels(true, true);

// Handle search
$posts = [];
$filters = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $filters = [
        'q' => $_GET['q'] ?? '',
        'start_date' => $_GET['start_date'] ?? '',
        'end_date' => $_GET['end_date'] ?? '',
        'location' => $_GET['location'] ?? '',
        'hashtag' => $_GET['hashtag'] ?? '',
        'user' => $_GET['user'] ?? '',
        'channel' => $_GET['channel'] ?? '',
    ];
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Only search if at least one filter is set
    if (array_filter($filters)) {
        $posts = $postModel->searchPosts($filters, $limit, $offset);
    }
}

// If AJAX request, return JSON
if (isset($_GET['ajax']) && isset($_GET['page'])) {
    header('Content-Type: application/json');
    
    $html = '';
    foreach ($posts as $post) {
        ob_start();
        include 'includes/post-card.php';
        $html .= ob_get_clean();
    }
    
    echo json_encode([
        'html' => $html,
        'hasMore' => count($posts) === 20,
        'page' => $page,
        'count' => count($posts)
    ]);
    exit;
}

include 'includes/header.php';
?>

<!-- Global Search -->
<div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form method="GET" action="/search.php" style="display: flex; gap: 10px; align-items: center;">
        <input type="text" 
               name="q" 
               placeholder="🔍 Global search: content, users, locations, channels, hashtags..." 
               value="<?= htmlspecialchars($filters['q'] ?? '') ?>"
               style="flex: 1; padding: 12px 15px; border: 2px solid #3a8f71; border-radius: 4px; font-size: 15px;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Search</button>
    </form>
</div>

<div class="search-filters">
    <h3>Advanced Filters</h3>
    <form method="GET" action="/search.php">
        <!-- Preserve global search if set -->
        <?php if (!empty($filters['q'])): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
        <?php endif; ?>
        
        <div class="filter-row">
            <div class="filter-group">
                <label>Date</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <input type="date" name="start_date" placeholder="Start Date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                    <input type="date" name="end_date" placeholder="End Date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
                </div>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="Search location..." value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label>Hashtag</label>
                <input type="text" name="hashtag" placeholder="Enter hashtag..." value="<?= htmlspecialchars($filters['hashtag'] ?? '') ?>">
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label>User</label>
                <input type="text" name="user" placeholder="Search user..." value="<?= htmlspecialchars($filters['user'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label>Channel</label>
                <select name="channel">
                    <option value="">All Channels</option>
                    <?php foreach ($channels as $channel): ?>
                        <?php if ($channel['type'] !== 'dm' && !$channel['is_hidden']): ?>
                            <option value="<?= htmlspecialchars($channel['id']) ?>" 
                                    <?= ($filters['channel'] ?? '') === $channel['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($channel['name']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="/search.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">Clear</a>
        </div>
    </form>
</div>

<div class="posts-feed" id="posts-feed">
    <?php if (empty($posts) && !array_filter($filters)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <p>Use the filters above to search for posts</p>
        </div>
    <?php elseif (empty($posts)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>No posts found matching your search criteria</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php include 'includes/post-card.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="loading-indicator" class="loading" style="display: none;">
    Loading more posts...
</div>

<script src="/assets/js/infinite-scroll.js"></script>

<?php include 'includes/footer.php'; ?>
