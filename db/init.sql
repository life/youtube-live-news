CREATE TABLE IF NOT EXISTS channels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    channel_id VARCHAR(100) NOT NULL UNIQUE,
    live_url VARCHAR(500),
    is_live BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS settings (
    key VARCHAR(50) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
);

INSERT INTO settings (key, value) VALUES ('quality', 'default') ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS recordings (
    id SERIAL PRIMARY KEY,
    channel_id INTEGER NOT NULL REFERENCES channels(id) ON DELETE CASCADE,
    channel_name VARCHAR(255) NOT NULL,
    video_id VARCHAR(50),
    duration INTEGER NOT NULL,
    filename VARCHAR(500),
    filesize BIGINT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'recording',
    pid INTEGER,
    started_at TIMESTAMP DEFAULT NOW(),
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Varsayılan kanallar
INSERT INTO channels (name, channel_id) VALUES
    ('CNN Türk', '@cnnturk'),
    ('TRT Haber', '@trthaber'),
    ('NTV', '@NTV'),
    ('Haber Global', '@haberglobal'),
    ('Atv Haber', '@Ahaber'),
    ('TV100', '@tv100'),
    ('Tvnet', '@TVNET'),
    ('Halk TV', '@/@Halktvkanali'),
    ('Sözcü TV', '@Sozcutelevizyonu'),
    ('Ülke Tv', '@ulketv'),
    ('Tgrt Haber', '@tgrthaber')
ON CONFLICT DO NOTHING;
