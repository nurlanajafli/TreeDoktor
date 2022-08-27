<?php

use Illuminate\Database\Migrations\Migration;

class SetTriggerToEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS eq_code_ai");
        DB::unprepared('CREATE TRIGGER eq_code_ai BEFORE INSERT ON equipments
            FOR EACH ROW
            BEGIN
                CASE
                    WHEN ISNULL(NEW.eq_code) THEN 
                        SET NEW.eq_code = NULL;
                    WHEN NEW.eq_code REGEXP "^[a-zA-Z0-9]+\-[0-9]+$" THEN 
                        CALL sp_setCustomVal("eq_code",SUBSTRING_INDEX(NEW.eq_code,"-",1),SUBSTRING_INDEX(NEW.eq_code,"-",-1));
                    ELSE 
                        SET NEW.eq_code = getNextCustomSeq("eq_code",NEW.eq_code);
                END CASE;
            END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS eq_code_ai");
    }
}
