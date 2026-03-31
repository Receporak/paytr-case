# PayTR Case

En düşük komisyonlu POS'u seçen REST API servisi. Harici bir kaynaktan POS komisyon oranlarını çeker, veritabanında saklar ve verilen kriterlere göre en uygun POS'u döner.

---

## Teknolojiler


| Teknoloji               | Versiyon |
| ----------------------- | -------- |
| PHP                     | 8.4      |
| Laravel                 | 13.x     |
| PostgreSQL              | 16       |
| Redis                   | 7        |
| Nginx                   | 1.27     |
| Docker / Docker Compose | -        |

---

## Kurulum

**Gereksinimler:** Docker, Docker Compose

```bash
cp .env.example .env
docker compose up -d --build
```

Container ayağa kalktığında `entrypoint.sh` otomatik olarak `composer install`, `key:generate` ve `migrate` adımlarını çalıştırır.

Uygulamaya erişin: `http://localhost:9000`

---

## Swagger Dokümantasyonu

```
http://localhost:9000/api/documentation
```

Yeniden oluşturmak için:

```bash
docker compose exec app php artisan l5-swagger:generate
```

## Test

```bash
docker compose exec app php artisan test
```
