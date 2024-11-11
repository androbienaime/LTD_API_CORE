<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('order.percentValuesProcessingOrder', 70);
        $this->migrator->add('order.defaultOrderStatus', "PendingState");
        $this->migrator->add('order.orderNumberFormat', "#00-#");
    }
};
