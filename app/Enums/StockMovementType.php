<?php
namespace App\Enums;

enum StockMovementType: string
{
    case CREATED = 'order_created';
    case COMPLETED = 'order_completed';
    case CANCELED = 'order_canceled';

    /**
     * Получить текстовое описание статуса
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Создание',
            self::COMPLETED => 'Завершена',
            self::CANCELED => 'Отмена',
        };
    }
}
