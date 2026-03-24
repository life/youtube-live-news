<?php

function startRecording(PDO $db, int $channelDbId, int $duration): array
{
    if (!in_array($duration, [5, 10, 15])) {
        throw new InvalidArgumentException('Süre 5, 10 veya 15 dakika olmalı');
    }

    $stmt = $db->prepare('SELECT * FROM channels WHERE id = ? AND is_live = true');
    $stmt->execute([$channelDbId]);
    $channel = $stmt->fetch();

    if (!$channel) {
        throw new RuntimeException('Kanal bulunamadı veya canlı yayın yok');
    }

    preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $channel['live_url'], $m);
    $videoId = $m[1] ?? '';

    if (!$videoId) {
        throw new RuntimeException('Video ID bulunamadı');
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $channel['name']);
    $filename = $safeName . '_' . date('Y-m-d_H-i-s') . '.mp4';
    $outputPath = '/var/www/html/public/recordings/' . $filename;
    $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
    $durationSeconds = $duration * 60;

    $cmd = sprintf(
        'nohup yt-dlp -f "best[height<=720]/best" --no-part '
        . '--download-sections "*0-%d" --force-keyframes-at-cuts '
        . '-o %s %s > /dev/null 2>&1 & echo $!',
        $durationSeconds,
        escapeshellarg($outputPath),
        escapeshellarg($watchUrl)
    );

    $pid = trim(shell_exec($cmd));

    if (!$pid || !is_numeric($pid)) {
        throw new RuntimeException('Kayıt başlatılamadı');
    }

    $stmt = $db->prepare(
        'INSERT INTO recordings (channel_id, channel_name, video_id, duration, filename, status, pid) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING *'
    );
    $stmt->execute([$channelDbId, $channel['name'], $videoId, $duration, $filename, 'recording', (int)$pid]);

    return $stmt->fetch();
}

function getRecordings(PDO $db): array
{
    $stmt = $db->query('SELECT * FROM recordings ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function stopRecording(PDO $db, int $recordingId): bool
{
    $stmt = $db->prepare('SELECT * FROM recordings WHERE id = ?');
    $stmt->execute([$recordingId]);
    $recording = $stmt->fetch();

    if (!$recording || $recording['status'] !== 'recording') {
        return false;
    }

    if ($recording['pid']) {
        exec('kill ' . (int)$recording['pid'] . ' 2>/dev/null');
    }

    $filesize = 0;
    $filepath = '/var/www/html/public/recordings/' . $recording['filename'];
    if (file_exists($filepath)) {
        $filesize = filesize($filepath);
    }

    $stmt = $db->prepare('UPDATE recordings SET status = ?, completed_at = NOW(), filesize = ? WHERE id = ?');
    $stmt->execute(['stopped', $filesize, $recordingId]);

    return true;
}

function deleteRecording(PDO $db, int $recordingId): bool
{
    $stmt = $db->prepare('SELECT * FROM recordings WHERE id = ?');
    $stmt->execute([$recordingId]);
    $recording = $stmt->fetch();

    if (!$recording) {
        return false;
    }

    if ($recording['status'] === 'recording' && $recording['pid']) {
        exec('kill ' . (int)$recording['pid'] . ' 2>/dev/null');
    }

    $filepath = '/var/www/html/public/recordings/' . $recording['filename'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    $stmt = $db->prepare('DELETE FROM recordings WHERE id = ?');
    $stmt->execute([$recordingId]);

    return true;
}

function checkAllActiveRecordings(PDO $db): void
{
    $stmt = $db->query("SELECT * FROM recordings WHERE status = 'recording'");
    $active = $stmt->fetchAll();

    foreach ($active as $rec) {
        $isRunning = false;
        if ($rec['pid']) {
            exec('kill -0 ' . (int)$rec['pid'] . ' 2>/dev/null', $out, $ret);
            $isRunning = ($ret === 0);
        }

        if (!$isRunning) {
            $filesize = 0;
            $filepath = '/var/www/html/public/recordings/' . $rec['filename'];
            if (file_exists($filepath)) {
                $filesize = filesize($filepath);
            }

            $status = $filesize > 0 ? 'completed' : 'failed';
            $stmt2 = $db->prepare('UPDATE recordings SET status = ?, completed_at = NOW(), filesize = ? WHERE id = ?');
            $stmt2->execute([$status, $filesize, $rec['id']]);
        }
    }
}
