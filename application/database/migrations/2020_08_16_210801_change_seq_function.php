<?php

use Illuminate\Database\Migrations\Migration;

class ChangeSeqFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
             
                SET @ret = (SELECT concat(sSeqGroup,'-',nLast_val));
                RETURN @ret;
            END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
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
    }
}
