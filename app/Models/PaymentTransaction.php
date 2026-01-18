<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Типы операций
     */
    const TYPE_DEPOSIT = 'deposit';         // Внесение депозита
    const TYPE_REFUND = 'refund';           // Возврат депозита
    const TYPE_TRANSFER_TO_MASTER = 'transfer_to_master'; // Перевод депозита мастеру
    const TYPE_WALLET_DEPOSIT = 'wallet_deposit'; // Зачисление на кошелек
    const TYPE_WALLET_WITHDRAW = 'wallet_withdraw'; // Списание с кошелька

    /**
     * Статусы транзакций
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELED = 'canceled';

    /**
     * Отношение к заявке
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Отношение к пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить красивое название типа
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_DEPOSIT => '💳 Оплата депозита',
            self::TYPE_REFUND => '↩️ Возврат депозита',
            self::TYPE_TRANSFER_TO_MASTER => '👨‍🔧 Перевод мастеру',
            self::TYPE_WALLET_DEPOSIT => '📥 Зачисление на кошелек',
            self::TYPE_WALLET_WITHDRAW => '📤 Списание с кошелька',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Получить красивое название статуса
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => '⏳ Ожидает',
            self::STATUS_COMPLETED => '✅ Завершено',
            self::STATUS_FAILED => '❌ Ошибка',
            self::STATUS_CANCELED => '🚫 Отменено',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Получить CSS класс для статуса
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELED => 'bg-gray-100 text-gray-800',
        ];

        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Получить CSS класс для типа
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            self::TYPE_DEPOSIT => 'bg-blue-100 text-blue-800',
            self::TYPE_REFUND => 'bg-yellow-100 text-yellow-800',
            self::TYPE_TRANSFER_TO_MASTER => 'bg-purple-100 text-purple-800',
            self::TYPE_WALLET_DEPOSIT => 'bg-green-100 text-green-800',
            self::TYPE_WALLET_WITHDRAW => 'bg-red-100 text-red-800',
        ];

        return $colors[$this->type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Проверить, является ли транзакция депозитной
     */
    public function isDepositType(): bool
    {
        return in_array($this->type, [
            self::TYPE_DEPOSIT,
            self::TYPE_REFUND,
            self::TYPE_TRANSFER_TO_MASTER,
        ]);
    }

    /**
     * Получить знак суммы (+ или -)
     */
    public function getAmountSignAttribute()
    {
        return in_array($this->type, [
            self::TYPE_DEPOSIT,
            self::TYPE_REFUND,
            self::TYPE_WALLET_DEPOSIT,
        ]) ? '+' : '-';
    }

    /**
     * Форматированная сумма со знаком
     */
    public function getFormattedAmountAttribute()
    {
        $sign = $this->amount_sign;
        return $sign . number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Создать транзакцию депозита
     */
    public static function createDepositTransaction(array $data): self
    {
        return self::create(array_merge([
            'type' => self::TYPE_DEPOSIT,
            'status' => self::STATUS_COMPLETED,
            'currency' => 'RUB',
        ], $data));
    }

    /**
     * Создать транзакцию возврата
     */
    public static function createRefundTransaction(array $data): self
    {
        return self::create(array_merge([
            'type' => self::TYPE_REFUND,
            'status' => self::STATUS_COMPLETED,
            'currency' => 'RUB',
        ], $data));
    }

    /**
     * Создать транзакцию перевода мастеру
     */
    public static function createTransferToMasterTransaction(array $data): self
    {
        return self::create(array_merge([
            'type' => self::TYPE_TRANSFER_TO_MASTER,
            'status' => self::STATUS_COMPLETED,
            'currency' => 'RUB',
        ], $data));
    }
}