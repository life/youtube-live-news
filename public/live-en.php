<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/channels.php';

$db = getDb();
$channels = getAllChannels($db, 'en');
$liveChannels = array_filter($channels, fn($c) => $c['is_live']);

// Get quality setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['quality']);
$quality = $stmt->fetchColumn() ?: 'default';

// Get grid columns setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['grid_columns']);
$gridColumns = (int)($stmt->fetchColumn() ?: 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live EN - YouTube Live News</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>YouTube Live News</h1>
        <nav>
            <a href="/">Canli TR</a>
            <a href="/live-en.php" class="active">Canli EN</a>
            <a href="/recording.php">Canli Kayit</a>
            <a href="/settings.php">Ayarlar</a>
        </nav>
        <button id="refreshBtn" onclick="refreshStreams()">Refresh</button>
    </header>

    <main>
        <div id="loading" class="loading" style="display:none;">Checking live streams...</div>

        <div id="grid" class="stream-grid grid-cols-<?= $gridColumns ?>">
            <?php if (empty($liveChannels)): ?>
                <div id="emptyState" class="empty-state">
                    <?php if (empty($channels)): ?>
                        <p>No channels added yet. Add channels from <a href="/settings.php">Settings</a>.</p>
                    <?php else: ?>
                        <p>No live streams found. Click "Refresh" to check again.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($liveChannels as $channel):
                    preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $channel['live_url'], $m);
                    $videoId = $m[1] ?? '';
                ?>
                    <div class="stream-card" data-video-id="<?= htmlspecialchars($videoId) ?>">
                        <div class="stream-header">
                            <span class="live-badge">LIVE</span>
                            <span class="channel-name"><?= htmlspecialchars($channel['name']) ?></span>
                        </div>
                        <div class="stream-video">
                            <div id="player-<?= htmlspecialchars($videoId) ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        var videoIds = <?= json_encode(array_values(array_map(function($c) {
            preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $c['live_url'], $m);
            return $m[1] ?? '';
        }, $liveChannels))) ?>;
        var streamQuality = <?= json_encode($quality) ?>;
        var hoverUnmute = <?php
            $stmt2 = $db->prepare('SELECT value FROM settings WHERE key = ?');
            $stmt2->execute(['hover_unmute']);
            $hu = $stmt2->fetchColumn() ?: '1';
            echo $hu === '1' ? 'true' : 'false';
        ?>;
    </script>
    <script src="/js/app.js"></script>
</body>
</html>
