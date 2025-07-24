<?php

namespace App\Enums;

/**
 * Статусы заказа
 */
enum OrderStatus: string
{

    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Получить текстовое описание статуса
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Активный',
            self::COMPLETED => 'Завершен',
            self::CANCELLED => 'Отменен',
        };
    }

}
