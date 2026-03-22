<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/channels.php';

$db = getDb();
$channels = getAllChannels($db);
$liveChannels = array_filter($channels, fn($c) => $c['is_live']);

// Get quality setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['quality']);
$quality = $stmt->fetchColumn() ?: 'default';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Live News</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>YouTube Live News</h1>
        <nav>
            <a href="/" class="active">Canlı Yayınlar</a>
            <a href="/settings.php">Ayarlar</a>
        </nav>
        <button id="refreshBtn" onclick="refreshStreams()">Yenile</button>
    </header>

    <main>
        <div id="loading" class="loading" style="display:none;">Canlı yayınlar kontrol ediliyor...</div>

        <div id="grid" class="stream-grid">
            <?php if (empty($liveChannels)): ?>
                <div id="emptyState" class="empty-state">
                    <?php if (empty($channels)): ?>
                        <p>Henüz kanal eklenmemiş. <a href="/settings.php">Ayarlar</a> sayfasından kanal ekleyin.</p>
                    <?php else: ?>
                        <p>Şu anda canlı yayın bulunamadı. "Yenile" butonuna tıklayarak tekrar deneyin.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($liveChannels as $channel):
                    // Extract video ID from embed URL
                    preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $channel['live_url'], $m);
                    $videoId = $m[1] ?? '';
                ?>
                    <div class="stream-card" data-video-id="<?= htmlspecialchars($videoId) ?>">
                        <div class="stream-header">
                            <span class="live-badge">CANLI</span>
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
    </script>
    <script src="/js/app.js"></script>
</body>
</html>
