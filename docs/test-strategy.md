# Стратегія тестування — GameFinder

## Підхід

Тестування проводиться на трьох рівнях:

| Рівень | Інструмент | Що перевіряється |
|--------|-----------|------------------|
| Unit-тести | PHPUnit | Сервіси, сутності, форми, бізнес-логіка |
| Інтеграційні тести | PHPUnit + Symfony Kernel | Контролери, взаємодія з БД |
| Статичний аналіз | PHP lint, Twig lint, Container lint | Синтаксис, конфігурація |

## Автоматизація (CI/CD)

При кожному push у репозиторій GitLab CI запускає пайплайн:

```
build:composer → lint:php-syntax
build:npm      → lint:twig        → test:unit
               → lint:container   → test:integration
```

**Що перевіряється автоматично:**
- Встановлення залежностей (composer, npm)
- Збірка frontend-ресурсів
- Синтаксис PHP та Twig-шаблонів
- Коректність DI-контейнера Symfony
- 94 тести (unit + integration)

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
| LobbyType (form) | `tests/Unit/Form/LobbyTypeTest.php` |

## Інтеграційні тести

| Компонент | Тест-файл |
|-----------|-----------|
| PremiumService | `tests/Integration/Service/PremiumServiceTest.php` |
| LobbyService | `tests/Integration/Service/LobbyServiceTest.php` |
| PremiumController | `tests/Integration/Controller/PremiumControllerTest.php` |

## Ключові сценарії, що тестуються

- Система відгуків: створення нового відгуку, оновлення існуючого, перерахунок рейтингу, відправка сповіщення
- Система лобі: створення кімнати, приєднання (публічне та приватне), обмеження на повні/закриті лобі, вихід учасника
- Модерація лобі: прийняття та відхилення заявок, зміна статусу учасника
- Валідація форм: вікові обмеження (від 6 до 60 років), максимальний вік більше мінімального
- Підбір гравців: алгоритм matchmaking на основі профілю та вподобань
- Сповіщення: генерація повідомлень при ключових діях користувачів

## Ручне тестування

Сценарії, які неможливо або недоцільно автоматизувати:
- OAuth-потоки (Google, Discord)
- Голосовий чат (WebRTC)
- Завантаження файлів та зображень
- Mercure real-time оновлення
- Premium оплата (LiqPay callback)

## Критерії проходження

- Всі unit-тести зелені (0 failures)
- Інтеграційні тести зелені
- Lint-перевірки без помилок
- Пайплайн завершується зі статусом `passed`
