<div class="post-card">
    <div class="post-header">
        <div class="post-avatar">
            <?= strtoupper(substr($post['first_name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="post-user-info">
            <div class="post-user-name">
                <?= htmlspecialchars(trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? ''))) ?>
            </div>
            <div class="post-meta">
                <?php 
                $date = new DateTime($post['created_at']);
                echo $date->format('d M Y, g:i a');
                ?>
                <?php if (!empty($post['channel_name'])): ?>
                    · in <a href="/channel.php?id=<?= urlencode($post['channel_id']) ?>" style="color: #3a8f71; text-decoration: none;"><?= htmlspecialchars($post['channel_name']) ?></a>
                <?php endif; ?>
                · ID: <?= htmlspecialchars($post['id']) ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($post['content'])): ?>
        <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($post['files'])): ?>
        <?php 
        $fileCount = count($post['files']);
        $gridClass = $fileCount === 1 ? 'grid-1' : ($fileCount === 2 ? 'grid-2' : ($fileCount <= 4 ? 'grid-3' : 'grid-more'));
        $displayFiles = array_slice($post['files'], 0, 6);
        $moreCount = $fileCount - 6;
        $postId = $post['id'];
        ?>
        <div class="post-images <?= $gridClass ?>" 
             data-post-id="<?= htmlspecialchars($postId) ?>"
             data-total-images="<?= $fileCount ?>">
            <?php foreach ($post['files'] as $index => $file): ?>
                <?php 
                // Construct image proxy URL with folder_id and file_name
                $imageSrc = (!empty($file['folder_id']) && !empty($file['file_name']))
                    ? '/image-proxy.php?folder_id=' . urlencode($file['folder_id']) . '&file=' . urlencode($file['file_name'])
                    : '/assets/images/workforce.jpeg';
                ?>
                <?php if ($index < 6): ?>
                    <div class="post-image <?= ($index === 5 && $moreCount > 0) ? 'more-images' : '' ?>" 
                         <?= ($index === 5 && $moreCount > 0) ? 'data-count="+' . $moreCount . '"' : '' ?>
                         data-image-index="<?= $index ?>"
                         data-image-src="<?= htmlspecialchars($imageSrc) ?>"
                         style="cursor: pointer;">
                        <?php if ($index < 5 || $moreCount === 0): ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Post image" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Hidden images for modal -->
                    <div class="post-image" style="display: none;" 
                         data-image-index="<?= $index ?>"
                         data-image-src="<?= htmlspecialchars($imageSrc) ?>">
                        <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Post image">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($post['place_name'])): ?>
        <div class="post-location">
            📍 <?= htmlspecialchars($post['place_name']) ?>
            <?php if (!empty($post['place_address'])): ?>
                - <?= htmlspecialchars($post['place_address']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($post['hashtags'])): ?>
        <div class="post-hashtags">
            <?php foreach ($post['hashtags'] as $hashtag): ?>
                <span class="hashtag">#<?= htmlspecialchars($hashtag['hashtag']) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="post-actions">
        <div class="post-action">
            ❤️ <?= $post['like_count'] ?>
        </div>
        <div class="post-action">
            💬 <?= $post['comment_count'] ?>
        </div>
    </div>
    
    <?php if (!empty($post['comments'])): ?>
        <div style="padding: 15px 20px; background: #f9f9f9; border-top: 1px solid #f0f0f0;">
            <?php foreach ($post['comments'] as $comment): ?>
                <div style="margin-bottom: 10px; font-size: 13px;">
                    <strong><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?>:</strong>
                    <?= htmlspecialchars($comment['content']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
