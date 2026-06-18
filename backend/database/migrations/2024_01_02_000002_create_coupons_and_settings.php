<?php
// 2024_01_02_000002_create_coupons_and_settings_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'flat', 'free_delivery']);
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order', 10, 2)->default(0);
            $table->integer('max_uses')->nullable();
            $table->integer('times_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });

        // Seed default settings
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            ['key' => 'store_name',              'value' => 'FreshBasket'],
            ['key' => 'store_email',             'value' => 'hello@freshbasket.com'],
            ['key' => 'store_phone',             'value' => '+1 (555) 000-0000'],
            ['key' => 'store_address',           'value' => '123 Market Street, New York, NY 10001'],
            ['key' => 'currency',                'value' => 'USD'],
            ['key' => 'tax_rate',                'value' => '8'],
            ['key' => 'free_delivery_threshold', 'value' => '50'],
            ['key' => 'base_delivery_fee',       'value' => '4.99'],
            ['key' => 'low_stock_threshold',     'value' => '10'],
            ['key' => 'maintenance_mode',        'value' => '0'],
            ['key' => 'stripe_enabled',          'value' => '1'],
            ['key' => 'paypal_enabled',          'value' => '1'],
            ['key' => 'cod_enabled',             'value' => '1'],
            ['key' => 'meta_title',              'value' => 'FreshBasket - Farm to Door'],
            ['key' => 'meta_description',        'value' => 'Premium grocery delivery from local farms.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('settings');
    }
};
