<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferenceTable extends Migration
{
    /**
     * @var string
     */
    private $table = 'reference';

    const DELETE_SOFT_REFERENCE_ARRAY = [
        'google_search' => 'Google Search',
        'facebook' => 'Facebook',
        'bing' => 'Bing',
        'print' => 'Print, Flyers, etc',
        'friend_or_neighbor' => 'Friend Or Neighbor',
        'other' => 'Other',
    ];
    const HIDE_REFERENCE_ARRAY = [
        'client' => 'Client',
        'user' => 'Employee',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->smallInteger('is_client_active')->default(0);
            $table->smallInteger('is_user_active')->default(0);
            $table->smallInteger('always_hidden')->default(0);
            $table->softDeletes();
        });
        //set soft delete references
        foreach (static::DELETE_SOFT_REFERENCE_ARRAY as $reference_slug => $reference_name) {
            DB::table($this->table)->insert([
                'slug' => $reference_slug,
                'name' => $reference_name
            ]);
        }
        //set show hide references
        foreach (static::HIDE_REFERENCE_ARRAY as $reference_slug => $reference_name) {
            DB::table($this->table)->insert([
                'slug' => $reference_slug,
                'name' => $reference_name,
                'is_' . $reference_slug . '_active' => 1
            ]);
        }

        DB::table($this->table)->insert([
            'slug' => 'Quickbooks',
            'name' => 'Quickbooks',
            'always_hidden' => 1
        ]);

        $CI = & get_instance();
        $refferenced_by_config = $CI->config->item('refferenced_by');
        $refferenced_by_config['Quickbooks'] = 'Quickbooks';

        DB::table('leads')->orderBy('lead_id')->whereNotNull('lead_reffered_by')->chunk(200, function ($leads) use ($refferenced_by_config) {
            foreach ($leads as $lead) {

                if(isset($refferenced_by_config[$lead->lead_reffered_by])) {
                    $reference = DB::table($this->table)->where('slug', $lead->lead_reffered_by)->first();

                    if (!is_null($reference)) {
                        DB::table('leads')->where(['lead_id' => $lead->lead_id])->update([
                            'lead_reffered_by' => $reference->id
                        ]);
                    } else {
                        $refernce_id = DB::table($this->table)->insertGetId([
                            'slug' => $lead->lead_reffered_by,
                            'name' => $refferenced_by_config[$lead->lead_reffered_by],
                        ]);
                        DB::table('leads')->where(['lead_id' => $lead->lead_id])->update([
                            'lead_reffered_by' => $refernce_id
                        ]);
                    }
                }
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->smallInteger('lead_reffered_by')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists($this->table);
    }
}
