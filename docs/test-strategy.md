# Стратегія тестування — GameFinder

## Підхід

Тестування проводиться на трьох рівнях:

| Рівень | Інструмент | Що перевіряється |
|--------|-----------|------------------|
| Unit-тести | PHPUnit | Сервіси, сутності, бізнес-логіка |
| Статичний аналіз | PHP lint, Twig lint, Container lint | Синтаксис, конфігурація |
| Ручне тестування | Браузер | UI, OAuth, WebRTC, файли |

## Автоматизація (CI/CD)

При кожному push у репозиторій GitLab CI запускає пайплайн:

```
build:composer → lint:php-syntax
build:npm      → lint:twig        → test:unit
               → lint:container
```

**Що перевіряється автоматично:**
- Встановлення залежностей (composer, npm)
- Збірка frontend-ресурсів
- Синтаксис PHP та Twig-шаблонів
- Коректність DI-контейнера Symfony
- 69 модульних тестів (сервіси + сутності)

**Результат:** JUnit XML-звіт (`var/log/junit.xml`)

## Покриття unit-тестами

| Компонент | Тест-файл |
|-----------|-----------|
| MatchmakingService | `tests/Unit/Service/MatchmakingServiceTest.php` |
| LobbyService | `tests/Unit/Service/LobbyServiceTest.php` |
| NotificationService | `tests/Unit/Service/NotificationServiceTest.php` |
| ReviewService | `tests/Unit/Service/ReviewServiceTest.php` |
| ProfileThemeService | `tests/Unit/Service/ProfileThemeServiceTest.php` |
| AvatarService | `tests/Unit/Service/AvatarServiceTest.php` |
| User (entity) | `tests/Unit/Entity/UserTest.php` |
| Lobby (entity) | `tests/Unit/Entity/LobbyTest.php` |

## Ручне тестування

Сценарії, які неможливо або недоцільно автоматизувати:
- OAuth-потоки (Google, Discord)
- Голосовий чат (WebRTC)
- Завантаження файлів та зображень
- Візуальна коректність UI
- Кросбраузерна сумісність

## Критерії проходження

- Всі unit-тести зелені (0 failures)
- Lint-перевірки без помилок
- Пайплайн завершується зі статусом `passed`
