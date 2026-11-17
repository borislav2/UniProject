<?php

use App\Support\OnePriceModel2026;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

// Един модел на цените навсякъде (17.11.2026): само месечен абонамент, без стартова цена и капаро. Вижте OnePriceModel2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $result = OnePriceModel2026::apply();
        if ($result['review']) {
            Log::warning('OnePriceModel2026: блокове с редактирани текстове, в които още има стари суми или капаро, за ръчен преглед: ' . implode(', ', $result['review']));
        }
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-17c`.
    }
};
