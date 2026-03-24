# YouTube Live News

YouTube haber kanallarının canlı yayınlarını tek bir sayfada yan yana izleme uygulaması.

## Ozellikler

- YouTube kanallarının canlı yayınlarını otomatik tespit eder
- Canlı yayınları ayarlanabilir grid düzeninde (3, 4, 5 veya 6 sütun) yan yana gosterir
- Fare ile ustune gelinen yayının sesi acılır, digerleri sessizde kalır (ayarlardan kapatılabilir)
- Ayarlar sayfasından kanal ekleme/silme
- Yayın kalitesi ayarı (240p - 4K)
- Ekran düzeni ayarı (3-6 sütun)
- Fare ile ses kontrolü açma/kapama ayarı
- 11 Turk haber kanalı varsayılan olarak yuklu gelir

## Kurulum

```bash
docker-compose up -d
```

Tarayıcıda ac: `http://localhost:1080`

## Kullanım

- **Ana sayfa**: Canlı yayınları gosterir. "Yenile" butonuyla yayınları kontrol eder.
- **Ayarlar** (`/settings.php`): Kanal ekleme/silme, yayın kalitesi, ekran düzeni ve fare ses kontrolü ayarları.

## Kanal Ekleme

Ayarlar sayfasından:
- **Kanal Adı**: Goruntulenen isim (orn: CNN Turk)
- **YouTube Kanal ID**: `@cnnturk` veya `UCxxxxxxxxx` formatında

## Teknolojiler

- PHP 8.3 (FPM)
- PostgreSQL 16
- Nginx
- YouTube IFrame Player API
- Docker Compose
