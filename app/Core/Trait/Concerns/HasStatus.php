<?php

namespace App\Core\Trait\Concerns;
trait HasStatus{
//    private $status;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BLOCKED = 'blocked';

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        if (!in_array($status, $this->getAvailableStatuses())) {
            throw new InvalidArgumentException("Invalid status: $status");
        }
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_BLOCKED,
        ];
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->getStatus() === self::STATUS_SUSPENDED;
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool{
        return $this->getStatus() === self::STATUS_BLOCKED;
    }

    /**
     * @return bool
     */
    public function isActive(): bool{
        return $this->getStatus() === self::STATUS_ACTIVE;
    }
}
