<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxesToEquipmentPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_parts', function (Blueprint $table) {
            $table->string('part_tax_name')->nullable()->after('part_price');
            $table->decimal('part_tax_rate', 10, 2)->default(1)->after('part_tax_name');
            $table->decimal('part_price_with_tax', 10, 2)->default(0)->after('part_tax_rate');
        });
        DB::unprepared("DROP TRIGGER IF EXISTS ins_eq_part_tax");
        DB::unprepared("DROP TRIGGER IF EXISTS upd_eq_part_tax");
        DB::unprepared('CREATE TRIGGER ins_eq_part_tax BEFORE INSERT ON equipment_parts
            FOR EACH ROW
            BEGIN
                IF NEW.part_tax_name IS NULL THEN 
                    SET NEW.part_tax_rate = 1;
                    SET NEW.part_price_with_tax = NEW.part_price;
                ELSE
                    SET NEW.part_price_with_tax = (NEW.part_price*NEW.part_tax_rate);
                END IF;
            END');
        DB::unprepared('CREATE TRIGGER upd_eq_part_tax BEFORE UPDATE ON equipment_parts
            FOR EACH ROW
            BEGIN
                IF NEW.part_tax_name IS NULL THEN 
                    SET NEW.part_tax_rate = 1;
                    SET NEW.part_price_with_tax = NEW.part_price;
                ELSE
                    SET NEW.part_price_with_tax = (NEW.part_price*NEW.part_tax_rate);
                END IF;
            END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS ins_eq_part_tax");
        DB::unprepared("DROP TRIGGER IF EXISTS upd_eq_part_tax");
        Schema::table('equipment_parts', function (Blueprint $table) {
            $table->dropColumn(['part_tax_name', 'part_tax_rate', 'part_price_with_tax']);
        });
    }
}
