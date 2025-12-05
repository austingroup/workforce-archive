<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Workforce Experience' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Workforce Experience</h1>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="/channels.php" class="<?= $activePage === 'channels' ? 'active' : '' ?>">
                            <span class="icon">📁</span>
                            <span>Channels</span>
                        </a>
                    </li>
                    <li>
                        <a href="/search.php" class="<?= $activePage === 'search' ? 'active' : '' ?>">
                            <span class="icon">🔍</span>
                            <span>Search</span>
                        </a>
                    </li>
                </ul>
                
                <?php if (!empty($channels)): ?>
                <div class="sidebar-section">Our Channels (<?= count($channels) ?>)</div>
                <ul>
                    <?php foreach ($channels as $channel): ?>
                        <?php if ($channel['type'] !== 'dm' && !$channel['is_hidden']): ?>
                        <li>
                            <a href="/channel.php?id=<?= urlencode($channel['id']) ?>" 
                               class="<?= isset($currentChannelId) && $currentChannelId === $channel['id'] ? 'active' : '' ?>">
                                <span class="icon">#</span>
                                <span><?= htmlspecialchars($channel['name']) ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="topbar">
                <div class="channel-info">
                    <h2><?= htmlspecialchars($pageTitle ?? 'Workforce Experience') ?></h2>
                    <?php if (isset($channelInfo)): ?>
                        <span class="member-count">👥 <?= $channelInfo['user_count'] ?? 0 ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="content-area">
