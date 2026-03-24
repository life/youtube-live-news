<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/channels.php';

$db = getDb();
$channels = getAllChannels($db);

// Get current quality setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['quality']);
$currentQuality = $stmt->fetchColumn() ?: 'default';

// Get current grid columns setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['grid_columns']);
$currentGridColumns = $stmt->fetchColumn() ?: '4';

// Get hover unmute setting
$stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
$stmt->execute(['hover_unmute']);
$currentHoverUnmute = $stmt->fetchColumn() ?: '1';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - YouTube Live News</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>YouTube Live News</h1>
        <nav>
            <a href="/">Canlı Yayınlar</a>
            <a href="/settings.php" class="active">Ayarlar</a>
        </nav>
    </header>

    <main>
        <section class="settings-section">
            <h2>Yayın Kalitesi</h2>
            <div class="quality-form">
                <label for="quality">Tüm yayınlar bu kalitede açılır:</label>
                <select id="quality" onchange="saveQuality(this.value)">
                    <?php
                    $qualities = [
                        'default' => 'Otomatik',
                        'small'   => '240p',
                        'medium'  => '360p',
                        'large'   => '480p',
                        'hd720'   => '720p',
                        'hd1080'  => '1080p',
                        'hd1440'  => '1440p',
                        'hd2160'  => '4K',
                    ];
                    foreach ($qualities as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $currentQuality === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <span id="qualitySaved" class="save-indicator" style="display:none;">Kaydedildi</span>
            </div>
        </section>

        <section class="settings-section">
            <h2>Fare ile Ses Kontrolü</h2>
            <div class="quality-form">
                <label for="hoverUnmute">Fare yayının üzerine gelince otomatik sesi aç:</label>
                <select id="hoverUnmute" onchange="saveHoverUnmute(this.value)">
                    <option value="1" <?= $currentHoverUnmute === '1' ? 'selected' : '' ?>>Açık</option>
                    <option value="0" <?= $currentHoverUnmute === '0' ? 'selected' : '' ?>>Kapalı</option>
                </select>
                <span id="hoverSaved" class="save-indicator" style="display:none;">Kaydedildi</span>
            </div>
        </section>

        <section class="settings-section">
            <h2>Ekran Düzeni</h2>
            <div class="quality-form">
                <label for="gridColumns">Canlı yayınlar satır başına kaç sütun gösterilsin:</label>
                <select id="gridColumns" onchange="saveGridColumns(this.value)">
                    <?php foreach ([3, 4, 5, 6] as $col): ?>
                        <option value="<?= $col ?>" <?= $currentGridColumns == $col ? 'selected' : '' ?>><?= $col ?> Sütun</option>
                    <?php endforeach; ?>
                </select>
                <span id="gridSaved" class="save-indicator" style="display:none;">Kaydedildi</span>
            </div>
        </section>

        <section class="settings-section">
            <h2>Kanal Ekle</h2>
            <form id="addChannelForm" class="add-form">
                <div class="form-group">
                    <label for="name">Kanal Adı</label>
                    <input type="text" id="name" name="name" placeholder="CNN Türk" required>
                </div>
                <div class="form-group">
                    <label for="channel_id">YouTube Kanal ID</label>
                    <input type="text" id="channel_id" name="channel_id" placeholder="@cabortime veya UCxxxxxxxxx" required>
                </div>
                <button type="submit">Ekle</button>
            </form>
        </section>

        <section class="settings-section">
            <h2>Kayıtlı Kanallar</h2>
            <div id="channelList">
                <?php if (empty($channels)): ?>
                    <p class="empty-state">Henüz kanal eklenmemiş.</p>
                <?php else: ?>
                    <table class="channel-table">
                        <thead>
                            <tr>
                                <th>Kanal Adı</th>
                                <th>Kanal ID</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($channels as $channel): ?>
                                <tr data-id="<?= $channel['id'] ?>">
                                    <td><?= htmlspecialchars($channel['name']) ?></td>
                                    <td><?= htmlspecialchars($channel['channel_id']) ?></td>
                                    <td>
                                        <?php if ($channel['is_live']): ?>
                                            <span class="live-badge">CANLI</span>
                                        <?php else: ?>
                                            <span class="offline-badge">Çevrimdışı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-delete" onclick="deleteChannel(<?= $channel['id'] ?>)">Sil</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
