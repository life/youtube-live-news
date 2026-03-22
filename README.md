# YouTube Live News

YouTube haber kanallarının canlı yayınlarını tek bir sayfada yan yana izleme uygulaması.

## Ozellikler

- YouTube kanallarının canlı yayınlarını otomatik tespit eder
- Tum canlı yayınları 4'lu grid halinde yan yana gosterir
- Fare ile ustune gelinen yayının sesi acılır, digerleri sessizde kalır
- Ayarlar sayfasından kanal ekleme/silme
- Yayın kalitesi ayarı (240p - 4K)
- 11 Turk haber kanalı varsayılan olarak yuklu gelir

## Kurulum

```bash
docker-compose up -d
```

Tarayıcıda ac: `http://localhost:1080`

## Kullanım

- **Ana sayfa**: Canlı yayınları gosterir. "Yenile" butonuyla yayınları kontrol eder.
- **Ayarlar** (`/settings.php`): Kanal ekleme/silme ve yayın kalitesi ayarı.

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
