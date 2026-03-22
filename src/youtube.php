<?php

function findLiveStream(string $channelId): ?array
{
    $url = "https://www.youtube.com/{$channelId}/live";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($html)) {
        return null;
    }

    // Check if there's an active live stream
    if (strpos($html, '"isLive":true') === false) {
        return null;
    }

    // Extract video ID
    if (preg_match('/"videoId":"([a-zA-Z0-9_-]{11})"/', $html, $matches)) {
        $videoId = $matches[1];
        return [
            'video_id' => $videoId,
            'embed_url' => "https://www.youtube.com/embed/{$videoId}?autoplay=1&mute=1",
            'watch_url' => "https://www.youtube.com/watch?v={$videoId}",
        ];
    }

    return null;
}

function refreshAllChannels(PDO $db): array
{
    $stmt = $db->query('SELECT id, channel_id FROM channels');
    $channels = $stmt->fetchAll();
    $results = [];

    foreach ($channels as $channel) {
        $live = findLiveStream($channel['channel_id']);

        if ($live) {
            $update = $db->prepare('UPDATE channels SET live_url = ?, is_live = TRUE, updated_at = NOW() WHERE id = ?');
            $update->execute([$live['embed_url'], $channel['id']]);
            $results[] = ['id' => $channel['id'], 'is_live' => true, 'live_url' => $live['embed_url']];
        } else {
            $update = $db->prepare('UPDATE channels SET live_url = NULL, is_live = FALSE, updated_at = NOW() WHERE id = ?');
            $update->execute([$channel['id']]);
            $results[] = ['id' => $channel['id'], 'is_live' => false, 'live_url' => null];
        }
    }

    return $results;
}
