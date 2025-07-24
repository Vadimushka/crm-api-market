https://docs.google.com/document/d/1WSVo8by4D13JUJKpB8m58itoHD_9YVRZXr2zr8sWa_U/edit?tab=t.0

# Trade Micro-CRM API (Laravel)

Это микро-CRM приложение для управления торговыми операциями, включая управление складами, товарами и заказами. API построено на Laravel с использованием RESTful принципов.

## Требования

* PHP 8.1+ 
* Composer 
* MySQL 5.7+ или MariaDB 10.3+ 
* Laravel 10+

## Установка

1. Клонировать репозиторий:
```shell
git clone git@github.com:Vadimushka/crm-api-market.git
cd crm-api-market
```
2. Установить зависимости:
```shell
composer install
```
3. Создать файл окружения и настроить подключение к БД:
```shell
cp .env.example .env
```
4. Сгенерировать ключ приложения:
```shell
php artisan key:generate
```
5. Запустить миграции и сидеры:
```shell
php artisan migrate --seed
```
6. Запустить сервер разработки:
```shell
php artisan serve
```

## API Endpoints

### Склады
* `GET /api/warehouses` - Получить список всех складов

### Товары
* `GET /api/products` - Получить список товаров с остатками по складам

### Заказы
* `GET /api/orders` - Получить список заказов (с пагинацией)
  * Параметры:
    * status - фильтр по статусу (active, completed, canceled)
    * search - фильтр по имени клиента (частичное совпадение)
    * warehouse_id - фильтр по ID склада 
    * date_from - фильтр по дате создания заказа 
    * date_to - фильтр по дате создания заказа 
    * per_page - количество элементов на странице (по умолчанию 15)
* `POST /api/orders` - Создать новый заказ
  * Тело запроса:
```json
{
    "customer": "Имя клиента",
    "warehouse_id": 1,
    "items": [
        {
            "product_id": 1,
            "count": 2
        },
        {
            "product_id": 3,
            "count": 1
        }
    ]
}
```
   
* `PUT /api/orders/{id}` - Обновить заказ (данные клиента и состав)
  * Тело запроса аналогично созданию заказа

* `POST /api/orders/{id}/complete` - Завершить заказ
  * Тело запроса пустое

* `POST /api/orders/{id}/cancel` - Отменить заказ
  * Тело запроса пустое

* `POST /api/orders/{id}/resume` - Возобновить отмененный заказ
  * Тело запроса пустое

## Фильтрация и пагинация

Пример запроса с фильтрацией:

```text
GET /api/orders?status=active&warehouse_id=1&per_page=5
```

Ответ включает метаданные пагинации:

```json
{
    "data": [...],
    "links": {...},
    "meta": {
        "current_page": 1,
        "per_page": 5,
        "total": 12,
        ...
    }
}
```

## Обработка ошибок

При ошибках API возвращает JSON в формате:

```json
{
    "message": "Описание ошибки",
    "errors": {
        "field": ["Ошибка валидации поля"]
    }
}
```

Статус коды:
* 200 - Успешный запрос 
* 201 - Успешное создание 
* 400 - Ошибка валидации 
* 404 - Ресурс не найден 
* 422 - Невозможно выполнить операцию (например, недостаточно товара для возобновления заказа)
* 500 - Внутренняя ошибка сервера

## Примеры использования

1. Создание заказа:
```shell
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
        "customer": "Иван Иванов",
        "warehouse_id": 1,
        "items": [
            {"product_id": 1, "count": 2},
            {"product_id": 3, "count": 1}
        ]
      }'
```
2. Завершение заказа:
```shell
curl -X POST http://localhost:8000/api/orders/1/complete \
  -H "Content-Type: application/json"
```

## Тестирование

Для запуска тестов:

```shell
php artisan test
```

Тесты покрывают:

* Создание и обновление заказов 
* Изменение статусов заказов 
* Корректное списание/возврат товаров 
* Валидацию входных данных
