// YouTube IFrame Player API
var players = {};

function onYouTubeIframeAPIReady() {
    if (typeof videoIds === 'undefined' || !videoIds.length) return;

    videoIds.forEach(function (id) {
        if (!id) return;
        players[id] = new YT.Player('player-' + id, {
            videoId: id,
            playerVars: {
                autoplay: 1,
                mute: 1,
                controls: 1,
                rel: 0,
                modestbranding: 1
            },
            events: {
                onReady: function (e) {
                    if (typeof streamQuality !== 'undefined' && streamQuality !== 'default') {
                        e.target.setPlaybackQuality(streamQuality);
                    }
                    setupHover(id);
                },
                onPlaybackQualityChange: function () {}
            }
        });
    });
}

function setupHover(videoId) {
    var card = document.querySelector('.stream-card[data-video-id="' + videoId + '"]');
    if (!card) return;

    card.addEventListener('mouseenter', function () {
        if (typeof hoverUnmute !== 'undefined' && !hoverUnmute) return;

        // Mute all others, unmute this one
        Object.keys(players).forEach(function (id) {
            if (players[id] && typeof players[id].mute === 'function') {
                if (id === videoId) {
                    players[id].unMute();
                    players[id].setVolume(100);
                } else {
                    players[id].mute();
                }
            }
        });
        card.classList.add('stream-active');
        document.querySelectorAll('.stream-card').forEach(function (c) {
            if (c !== card) c.classList.remove('stream-active');
        });
    });
}

function refreshStreams() {
    var btn = document.getElementById('refreshBtn');
    var loading = document.getElementById('loading');
    var grid = document.getElementById('grid');

    btn.disabled = true;
    btn.textContent = 'Kontrol ediliyor...';
    loading.style.display = 'block';

    fetch('/api/refresh.php', { method: 'POST' })
        .then(function (res) { return res.json(); })
        .then(function () {
            window.location.reload();
        })
        .catch(function (err) {
            alert('Hata: ' + err.message);
            btn.disabled = false;
            btn.textContent = 'Yenile';
            loading.style.display = 'none';
        });
}

function deleteChannel(id) {
    if (!confirm('Bu kanalı silmek istediğinize emin misiniz?')) return;

    fetch('/api/channels.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.deleted) {
                var row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) row.remove();

                var tbody = document.querySelector('.channel-table tbody');
                if (tbody && tbody.children.length === 0) {
                    document.getElementById('channelList').innerHTML =
                        '<p class="empty-state">Henüz kanal eklenmemiş.</p>';
                }
            }
        })
        .catch(function (err) {
            alert('Hata: ' + err.message);
        });
}

// Save hover unmute setting
function saveHoverUnmute(value) {
    var indicator = document.getElementById('hoverSaved');

    fetch('/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: 'hover_unmute', value: String(value) })
    })
        .then(function (res) {
            if (!res.ok) throw new Error('Sunucu hatası: ' + res.status);
            return res.json();
        })
        .then(function () {
            if (indicator) {
                indicator.style.display = 'inline';
                setTimeout(function () { indicator.style.display = 'none'; }, 2000);
            }
        })
        .catch(function (err) {
            alert('Hata: ' + err.message);
        });
}

// Save grid columns setting
function saveGridColumns(value) {
    var indicator = document.getElementById('gridSaved');

    fetch('/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: 'grid_columns', value: String(value) })
    })
        .then(function (res) {
            if (!res.ok) throw new Error('Sunucu hatası: ' + res.status);
            return res.json();
        })
        .then(function () {
            if (indicator) {
                indicator.style.display = 'inline';
                setTimeout(function () { indicator.style.display = 'none'; }, 2000);
            }
        })
        .catch(function (err) {
            alert('Ekran düzeni kaydedilemedi: ' + err.message);
        });
}

// Save quality setting
function saveQuality(value) {
    var indicator = document.getElementById('qualitySaved');

    fetch('/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: 'quality', value: value })
    })
        .then(function (res) { return res.json(); })
        .then(function () {
            if (indicator) {
                indicator.style.display = 'inline';
                setTimeout(function () { indicator.style.display = 'none'; }, 2000);
            }
        })
        .catch(function (err) {
            alert('Hata: ' + err.message);
        });
}

// Add channel form
var form = document.getElementById('addChannelForm');
if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var name = document.getElementById('name').value.trim();
        var channelId = document.getElementById('channel_id').value.trim();

        if (!name || !channelId) return;

        fetch('/api/channels.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, channel_id: channelId })
        })
            .then(function (res) {
                if (!res.ok) return res.json().then(function (d) { throw new Error(d.error); });
                return res.json();
            })
            .then(function () {
                window.location.reload();
            })
            .catch(function (err) {
                alert('Hata: ' + err.message);
            });
    });
}
