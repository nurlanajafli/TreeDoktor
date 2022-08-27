<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferencesWeight extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reference', function (Blueprint $table) {
            $table->integer('weight')->default(0);
        });

        $reference = DB::table('reference')->orderBy('name', 'asc')->get();
        if($reference)
        {
            foreach ($reference as $key => $value){
                DB::table('reference')->where('id', $value->id)->update(['weight' => $key]);
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reference', function (Blueprint $table) {
            $table->dropColumn(['weight']);
        });
    }
}
