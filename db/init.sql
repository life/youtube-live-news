CREATE TABLE IF NOT EXISTS channels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    channel_id VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(10) DEFAULT 'tr',
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

-- Varsayılan TR kanalları
INSERT INTO channels (name, channel_id, category) VALUES
    ('CNN Türk', '@cnnturk', 'tr'),
    ('TRT Haber', '@trthaber', 'tr'),
    ('NTV', '@NTV', 'tr'),
    ('Haber Global', '@haberglobal', 'tr'),
    ('Atv Haber', '@Ahaber', 'tr'),
    ('TV100', '@tv100', 'tr'),
    ('Tvnet', '@TVNET', 'tr'),
    ('Halk TV', '@Halktvkanali', 'tr'),
    ('Sözcü TV', '@Sozcutelevizyonu', 'tr'),
    ('Ülke Tv', '@ulketv', 'tr'),
    ('Tgrt Haber', '@tgrthaber', 'tr')
ON CONFLICT DO NOTHING;

-- Varsayılan EN kanalları
INSERT INTO channels (name, channel_id, category) VALUES
    ('CNN International', '@cnni', 'en'),
    ('BBC News', '@BBCNews', 'en'),
    ('Al Jazeera English', '@AlJazeeraEnglish', 'en'),
    ('Sky News', '@SkyNews', 'en'),
    ('Fox News', '@FoxNews', 'en'),
    ('CNBC', '@CNBC', 'en'),
    ('ABC News', '@ABCNews', 'en'),
    ('DW News', '@DWNews', 'en'),
    ('France 24 English', '@FRANCE24English', 'en'),
    ('Euronews', '@euronews', 'en'),
    ('TRT World', '@TRTWorld', 'en'),
    ('WION', '@WIONews', 'en'),
    ('Bloomberg', '@Bloomberg', 'en'),
    ('NBC News', '@NBCNews', 'en'),
    ('CBS News', '@CBSNews', 'en'),
    ('CNA', '@channelnewsasia', 'en'),
    ('India Today', '@IndiaToday', 'en'),
    ('GB News', '@GBNewsOnline', 'en'),
    ('LiveNOW from FOX', '@LiveNOWFOX', 'en')
ON CONFLICT DO NOTHING;
