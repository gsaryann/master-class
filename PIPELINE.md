# CI/CD Pipeline

Проект использует GitHub Actions. Конфигурация находится в `.github/workflows/ci.yml`.

## Ветки

- `develop` - development.
- `uat` - UAT.
- `main` - production.

Пайплайн запускается на `push` и `pull_request`.

## Шаги пайплайна

1. Tests

Запускается:

```bash
php artisan test --coverage --min=50
```

Пайплайн падает, если тесты завершились с ошибкой или покрытие ниже 50%.

2. Static analysis

Запускается Larastan/PHPStan:

```bash
vendor/bin/phpstan analyse --error-format=github
```

Пайплайн падает при любой ошибке статического анализа.

3. Linting

На долгоживущих ветках и в pull request запускается проверка Laravel Pint:

```bash
vendor/bin/pint --test
```

На остальных push-ветках запускается автоформатирование:

```bash
vendor/bin/pint
```

4. Deploy simulation

Шаг выполняется только после успешных тестов, анализа и linting.

- `develop`: используется `.env.dev`, выводится `Deploying to DEV with .env.dev`.
- `uat`: используется `.env.uat`, выводится `Deploying to UAT with .env.uat`.
- `main`: используется `.env.prod`, выводится `Deploying to PROD with .env.prod`.

Для production используется GitHub Environment `production`, где нужно включить ручной approve через Required reviewers.

## Файлы окружений

- `.env.dev`
- `.env.uat`
- `.env.prod`
- `.env.ci`

Основной `.env` добавлен в `.gitignore` и не отправляется в репозиторий.
