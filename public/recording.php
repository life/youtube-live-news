<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/channels.php';
require_once __DIR__ . '/../src/recording.php';

function formatSize(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

$db = getDb();
$channels = getAllChannels($db);
$liveChannels = array_filter($channels, fn($c) => $c['is_live']);
checkAllActiveRecordings($db);
$recordings = getRecordings($db);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canlı Kayıt - YouTube Live News</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>YouTube Live News</h1>
        <nav>
            <a href="/">Canlı Yayınlar</a>
            <a href="/recording.php" class="active">Canlı Kayıt</a>
            <a href="/settings.php">Ayarlar</a>
        </nav>
    </header>

    <main>
        <section class="settings-section">
            <h2>Yeni Kayıt Başlat</h2>
            <?php if (empty($liveChannels)): ?>
                <p class="empty-state-inline">Şu anda canlı yayın yok. <a href="/">Ana sayfa</a>dan yayınları yenileyin.</p>
            <?php else: ?>
                <div class="record-form">
                    <div class="form-group">
                        <label for="recordChannel">Kanal</label>
                        <select id="recordChannel">
                            <?php foreach ($liveChannels as $ch): ?>
                                <option value="<?= $ch['id'] ?>"><?= htmlspecialchars($ch['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recordDuration">Süre</label>
                        <select id="recordDuration">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Dakika</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button id="startRecordBtn" onclick="startRecordingAction()">Kaydı Başlat</button>
                </div>
            <?php endif; ?>
        </section>

        <section class="settings-section">
            <h2>Kayıtlar</h2>
            <div id="recordingsContainer">
                <?php if (empty($recordings)): ?>
                    <p class="empty-state-inline" id="noRecordings">Henüz kayıt yapılmamış.</p>
                <?php else: ?>
                    <table class="channel-table" id="recordingsTable">
                        <thead>
                            <tr>
                                <th>Kanal</th>
                                <th>Süre</th>
                                <th>Durum</th>
                                <th>Boyut</th>
                                <th>Tarih</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recordings as $rec): ?>
                                <tr data-id="<?= $rec['id'] ?>">
                                    <td><?= htmlspecialchars($rec['channel_name']) ?></td>
                                    <td><?= $rec['duration'] ?> dk</td>
                                    <td>
                                        <?php if ($rec['status'] === 'recording'): ?>
                                            <span class="recording-badge">Kaydediliyor...</span>
                                        <?php elseif ($rec['status'] === 'completed'): ?>
                                            <span class="completed-badge">Tamamlandı</span>
                                        <?php elseif ($rec['status'] === 'stopped'): ?>
                                            <span class="offline-badge">Durduruldu</span>
                                        <?php else: ?>
                                            <span class="offline-badge">Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $rec['filesize'] > 0 ? formatSize($rec['filesize']) : '-' ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($rec['started_at'])) ?></td>
                                    <td class="action-buttons">
                                        <?php if ($rec['status'] === 'recording'): ?>
                                            <button class="btn-stop" onclick="stopRecordingAction(<?= $rec['id'] ?>)">Durdur</button>
                                        <?php endif; ?>
                                        <?php if (in_array($rec['status'], ['completed', 'stopped']) && $rec['filesize'] > 0): ?>
                                            <a href="/recordings/<?= htmlspecialchars($rec['filename']) ?>" class="btn-download" download>İndir</a>
                                        <?php endif; ?>
                                        <button class="btn-delete" onclick="deleteRecordingAction(<?= $rec['id'] ?>)">Sil</button>
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
