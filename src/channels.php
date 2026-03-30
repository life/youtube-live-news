<?php

function getAllChannels(PDO $db, ?string $category = null): array
{
    if ($category) {
        $stmt = $db->prepare('SELECT * FROM channels WHERE category = ? ORDER BY created_at DESC');
        $stmt->execute([$category]);
    } else {
        $stmt = $db->query('SELECT * FROM channels ORDER BY created_at DESC');
    }
    return $stmt->fetchAll();
}

function addChannel(PDO $db, string $name, string $channelId, string $category = 'tr'): array
{
    // Ensure channel_id starts with @
    if (!str_starts_with($channelId, '@') && !str_starts_with($channelId, 'UC')) {
        $channelId = '@' . $channelId;
    }

    $stmt = $db->prepare('INSERT INTO channels (name, channel_id, category) VALUES (?, ?, ?) RETURNING *');
    $stmt->execute([$name, $channelId, $category]);
    return $stmt->fetch();
}

function deleteChannel(PDO $db, int $id): bool
{
    $stmt = $db->prepare('DELETE FROM channels WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
