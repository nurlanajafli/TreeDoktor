<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AddDefaultEquipmentGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $default_groups = [
            'vehicles',
            'loaders',
            'trailers',
            'wood chippers',
            'stump grinders',
            'sold or deceased equipment'
        ];
        $default_prefix[0] = 'VHC';
        $default_prefix[1] = 'LDR';
        $default_prefix[2] = 'TRL';
        $default_prefix[3] = 'CHPR';
        $default_prefix[4] = 'SG';
        $default_prefix[5] = 'SOLD';

        $default_colors[0] = '#008f26';
        $default_colors[1] = '#1e11d6';
        $default_colors[2] = '#009c82';
        $default_colors[3] = '#871387';
        $default_colors[4] = '#0081c2';
        $default_colors[5] = '#dd0af0';

        $isset_groups = DB::table('equipment_groups')->get();
        if ($isset_groups) {
            foreach ($isset_groups as $key=>$val) {
                $name = mb_strtolower($val->group_name);
                if(array_search($name, $default_groups) !== false){
                    DB::table('equipment_groups')->where(['group_id'=>$val->group_id])->update(['group_prefix'=>$default_prefix[array_search($name, $default_groups)]]);

                }
            }

        }
        else {
            foreach ($default_groups as $key=>$val) {
                DB::table('equipment_groups')->insert(['group_name' => ucwords($val), 'group_prefix' => $default_prefix[$key]]);
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
        $default_groups = [
            'vehicles',
            'loaders',
            'trailers',
            'wood chippers',
            'stump grinders',
            'sold or deceased equipment'
        ];
        $isset_groups = DB::table('equipment_groups')->get();
        if ($isset_groups) {
            foreach ($isset_groups as $key=>$val) {
                $name = mb_strtolower($val->group_name);
                if(array_search($name, $default_groups) !== false){
                    DB::table('equipment_groups')->where(['group_id'=>$val->group_id])->update(['group_prefix'=>NULL]);

                }
            }

        }
    }
}
