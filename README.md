# Survey API — Сервис опросов и голосований

REST API для создания опросов, прохождения и анализа результатов.

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

## API Endpoints

### Аутентификация

| Метод | URL | Описание |
|-------|-----|----------|
| `POST` | `/api/register` | Регистрация |
| `POST` | `/api/login` | Вход |

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

---

## База данных

### Справочники

| Таблица | Описание |
|---------|----------|
| `roles` | Роли: author, respondent |
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

## Тестирование API

### Примеры запросов (curl для Windows)

> **Важно:** Заголовок `Accept: application/json` обязателен для API-ответов Laravel.

**Регистрация:**
```cmd
curl -X POST http://localhost:8000/api/register -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"name\":\"John\",\"email\":\"john@example.com\",\"password\":\"password123\"}"
```

**Вход:**
```cmd
curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"email\":\"john@example.com\",\"password\":\"password123\"}"
```

**Создание опроса (с токеном):**
```cmd
curl -X POST http://localhost:8000/api/surveys -H "Content-Type: application/json" -H "Accept: application/json" -H "Authorization: Bearer <ваш_токен>" -d "{\"title\":\"Мой опрос\",\"description\":\"Описание\"}"
```

**Список опросов:**
```cmd
curl -X GET http://localhost:8000/api/surveys -H "Accept: application/json"
```

**Добавить вопрос:**
```cmd
curl -X POST http://localhost:8000/api/surveys/1/questions -H "Content-Type: application/json" -H "Accept: application/json" -H "Authorization: Bearer <ваш_токен>" -d "{\"type\":\"single_choice\",\"text\":\"Ваш любимый цвет?\",\"order\":1,\"required\":true}"
```

**Пройти опрос:**
```cmd
curl -X POST http://localhost:8000/api/surveys/1/respond -H "Content-Type: application/json" -H "Accept: application/json" -H "Authorization: Bearer <ваш_токен>" -d "{\"answers\":[{\"question_id\":1,\"option_id\":1},{\"question_id\":2,\"option_ids\":[1,3]},{\"question_id\":3,\"text_value\":\"Текстовый ответ\"}]}"
```

**Статистика по опросу:**
```cmd
curl -X GET http://localhost:8000/api/surveys/1/results -H "Accept: application/json" -H "Authorization: Bearer <ваш_токен>"
```

> **Примечание:** В Windows `curl` не поддерживает перенос строк через `\`. Все параметры указываются в одной строке.

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
│   └── .env                      # Конфигурация
├── docs/
│   ├── er-diagram.png            # ER-диаграмма
│   └── survey_api.sql            # SQL-схема
├── tests/                        # Автотесты
├── README.md                     # Этот файл
├── survey-api.md                 # Предметная область проекта
└── Task.md                       # Задание практики
```

---

## 📚 Документация

- [План разработки](./plans/survey-api-laravel.md)
- [Отчёты](./reports/)
- [Задание практики](./Task.md)

---

**Автор:** Студент группы 1ИСП-21 Авхимович Артём
**Год:** 2026
