<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSequenceTableAndFunctions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('_sequences', function (Blueprint $table) {
            $table->string('seq_name', 50);
            $table->string('seq_group', 10);
            $table->unsignedInteger('seq_val');
            $table->unique(['seq_name', 'seq_group']);
        });
        DB::unprepared("DROP FUNCTION IF EXISTS getNextCustomSeq");
        DB::unprepared("CREATE FUNCTION getNextCustomSeq
            (
                sSeqName VARCHAR(50),
                sSeqGroup VARCHAR(10)
            ) RETURNS VARCHAR(20)
            BEGIN
                DECLARE nLast_val INT; 
             
                SET nLast_val =  (SELECT seq_val
                                      FROM _sequences
                                      WHERE seq_name = sSeqName
                                            AND seq_group = sSeqGroup);
                IF nLast_val IS NULL THEN
                    SET nLast_val = 1;
                    INSERT INTO _sequences (seq_name,seq_group,seq_val)
                    VALUES (sSeqName,sSeqGroup,nLast_Val);
                ELSE
                    SET nLast_val = nLast_val + 1;
                    UPDATE _sequences SET seq_val = nLast_val
                    WHERE seq_name = sSeqName AND seq_group = sSeqGroup;
                END IF; 
             
                SET @ret = (SELECT concat(sSeqGroup,'-',lpad(nLast_val,6,'0')));
                RETURN @ret;
            END");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_setCustomVal");
        DB::unprepared("CREATE PROCEDURE sp_setCustomVal(sSeqName VARCHAR(50),  
                          sSeqGroup VARCHAR(10), nVal INT UNSIGNED)
            BEGIN
                IF (SELECT COUNT(*) FROM _sequences  
                        WHERE seq_name = sSeqName  
                            AND seq_group = sSeqGroup) = 0 THEN
                    INSERT INTO _sequences (seq_name,seq_group,seq_val)
                    VALUES (sSeqName,sSeqGroup,nVal);
                ELSE
                IF (SELECT MAX(seq_val) FROM _sequences WHERE seq_name = sSeqName  
                            AND seq_group = sSeqGroup) < nVal THEN
                    UPDATE _sequences SET seq_val = nVal
                    WHERE seq_name = sSeqName AND seq_group = sSeqGroup;
                    END IF;
                END IF;
            END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_sequences');
        DB::unprepared("DROP FUNCTION IF EXISTS getNextCustomSeq");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_setCustomVal");
    }
}
