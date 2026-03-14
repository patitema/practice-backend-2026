# Survey API — Сервис опросов и голосований

REST API для создания опросов, прохождения и анализа результатов.

![Tests](https://img.shields.io/badge/tests-29%20passed-green)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Docker](https://img.shields.io/badge/Docker-ready-blue)

---

## О проекте

**Survey API** — это платформа для создания анкет и голосований с различными типами вопросов, сбора ответов от респондентов и анализа результатов.

**Возможности:**
- Создание опросов с вопросами разных типов (одиночный выбор, множественный выбор, текстовый ответ)
- Управление жизненным циклом опроса (черновик → опубликован → закрыт)
- Прохождение опросов респондентами
- Аналитика и экспорт результатов

**Стек:**
- PHP 8.4
- Laravel 12
- MySQL 8.4
- Laravel Sanctum (JWT-аутентификация)

---

## Быстрый старт

### Требования

- PHP = 8.4
- Composer
- MySQL = 8.4
- Git

### Установка

1. **Клонируйте репозиторий**
   ```bash
   git clone https://github.com/patitema/practice-backend-2026.git 
   cd practice-backend-2026
   ```

2. **Перейдите в папку проекта**
   ```bash
   cd src
   ```

3. **Установите зависимости**
   ```bash
   composer install
   ```

4. **Настройте окружение**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Настройте подключение к БД** (файл `.env`)
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=survey_api
   DB_USERNAME=root
   DB_PASSWORD=ваш_пароль
   ```

6. **Создайте базу данных**
   ```bash
   mysql -u root -p -e "CREATE DATABASE survey_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

7. **Запустите миграции и сидеры**
   ```bash
   php artisan migrate --seed
   ```

8. **Запустите сервер**
   ```bash
   php artisan serve
   ```

API доступен по адресу: `http://localhost:8000/api`

---

## 🐳 Docker

### Требования

- Docker
- Docker Compose

### Быстрый старт

1. **Запуск контейнеров**
   ```bash
   docker-compose up -d
   ```

2. **Запуск миграций**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

3. **Запуск тестов**
   ```bash
   docker-compose exec app php artisan test
   ```

4. **Остановка**
   ```bash
   docker-compose down
   ```

### Сервисы

| Сервис | URL | Описание |
|--------|-----|----------|
| API | http://localhost:8000 | Laravel приложение |
| phpMyAdmin | http://localhost:8080 | Веб-интерфейс БД |
| MySQL | localhost:3306 | База данных |

### Полезные команды

```bash
# Просмотр логов
docker-compose logs -f app

# Доступ к shell контейнера
docker-compose exec app bash

# Перезапуск
docker-compose restart

# Полная пересборка
docker-compose up -d --build
```

---

## API Endpoints

### Аутентификация

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `POST` | `/api/register` | Регистрация | Нет |
| `POST` | `/api/login` | Вход | Нет |
| `POST` | `/api/logout` | Выход | Да |

### Опросы

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `GET` | `/api/surveys` | Список опубликованных | Нет |
| `GET` | `/api/surveys/{id}` | Детали опроса | Нет |
| `POST` | `/api/surveys` | Создать опрос | Да |
| `PUT` | `/api/surveys/{id}` | Редактировать опрос | Да |
| `POST` | `/api/surveys/{id}/publish` | Опубликовать | Да |
| `POST` | `/api/surveys/{id}/close` | Закрыть опрос | Да |
| `DELETE` | `/api/surveys/{id}` | Удалить опрос | Да |

### Вопросы

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `POST` | `/api/surveys/{id}/questions` | Добавить вопрос | Да |
| `PUT` | `/api/questions/{id}` | Редактировать вопрос | Да |
| `DELETE` | `/api/questions/{id}` | Удалить вопрос | Да |

### Варианты ответов

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `POST` | `/api/questions/{id}/options` | Добавить вариант | Да |
| `PUT` | `/api/options/{id}` | Редактировать вариант | Да |
| `DELETE` | `/api/options/{id}` | Удалить вариант | Да |

### Прохождение опросов

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `POST` | `/api/surveys/{id}/respond` | Пройти опрос | Да |

### Аналитика

| Метод | URL | Описание | Auth |
|-------|-----|----------|------|
| `GET` | `/api/surveys/{id}/results` | Статистика | Да |
| `GET` | `/api/surveys/{id}/results/export` | Экспорт в JSON | Да |

> **Примечание:** Для защищённых эндпоинтов передавайте токен в заголовке:
> `Authorization: Bearer <ваш_токен>`

### Права доступа

| Роль | Создание опросов | Редактирование | Удаление | Статистика | Прохождение |
|------|-----------------|----------------|----------|------------|-------------|
| **author** | ✅ Свои | ✅ Свои (черновик) | ✅ Свои | ✅ Свои | ✅ |
| **admin** | ❌ | ❌ | ✅ Любые | ✅ Любые | ❌ |
| **respondent** | ❌ | ❌ | ❌ | ❌ | ✅ |

> **Примечание:** Роль `admin` назначается вручную через БД:
> ```sql
> UPDATE users SET role = 3 WHERE email = 'admin@example.com';
> ```

Полная документация API доступна в файле [`survey-api.md`](./survey-api.md).

---

## База данных

### Справочники

| Таблица | Описание |
|---------|----------|
| `roles` | Роли: author (1), respondent (2), **admin (3)** |
| `statuses` | Статусы: draft, published, closed |
| `types` | Типы вопросов: single_choice, multiple_choice, text_answer |

### Основные таблицы

| Таблица | Описание |
|---------|----------|
| `users` | Пользователи |
| `surveys` | Опросы |
| `questions` | Вопросы |
| `options` | Варианты ответов |
| `responses` | Ответы респондентов |
| `answers` | Детали ответов |

ER-диаграмма: [`docs/er-diagram.png`](./docs/er-diagram.png)

---

## 📁 Структура проекта

```
practice-backend-2026/
├── src/                          # Laravel приложение
│   ├── app/
│   │   ├── Http/Controllers/Api/ # Контроллеры
│   │   └── Models/               # Модели
│   ├── database/
│   │   ├── migrations/           # Миграции
│   │   └── seeders/              # Сидеры
│   ├── routes/
│   │   └── api.php               # API маршруты
│   ├── tests/                    # Автотесты
│   └── .env                      # Конфигурация
├── docs/
│   ├── er-diagram.png            # ER-диаграмма
│   ├── Survey API.openapi.json   # OpenAPI спецификация 
│   ├── Survey API.apidog.json    # ApiDog спецификация
│   ├── Survey API.postman.json   # Postman коллекция
│   └── survey_api.sql            # SQL-схема
├── README.md                     # Этот файл
├── survey-api.md                 # Предметная область проекта
└── Task.md                       # Задание практики
```

---

## 📚 Документация

- [Survey API — предметная область](./survey-api.md)
- [ER-диаграмма](./docs/er-diagram.png)
- [OpenAPI спецификация](./docs/Survey%20API.openapi.json)
- [Postman коллекция](./docs/Survey%20API.postman.json)
- [Задание практики](./Task.md)

---

**Автор:** Студент группы 1ИСП-21 Авхимович Артём
**Год:** 2026
