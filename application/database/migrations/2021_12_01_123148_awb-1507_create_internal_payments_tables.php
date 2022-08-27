<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AWB1507CreateInternalPaymentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount');
            $table->unsignedInteger('transaction_id');
            $table->integer('payment_alarm')->nullable();
            $table->boolean('checked')->default(0);
            $table->unsignedInteger('paymentable_id');
            $table->string('paymentable_type');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        DB::table('settings')->insert([
            'stt_key_name'      => 'int_pay_driver',
            'stt_key_value'     => 'bambora',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'payment_internal_bambora',
            'stt_key_value'     => '{'.
                                        '"merchantID": "300207718",' .
                                        '"apiKeys": {' .
                                            '"payments": "4a697Ebd9939415d9e3F0576ceB8D2CB",' .
                                            '"reporting": "baA85C9071B3462aAe0F5d452c024C5F",' .
                                            '"profiles": "2D4E5A0C3E0A4AD98CD44215BCB47D24"' .
                                        '},' .
                                        '"apiVersion": "v1",' .
                                        '"platform": "api"' .
                                    '}',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'payment_internal_authorize',
            'stt_key_value'     => '{' .
                                        '"loginId": "6P2b5pPT",' .
                                        '"transactionKey": "226hc52ghND23VKK",' .
                                        '"publicKey": "Simon",' .
                                        '"isChase": true,' .
                                        '"isSandbox": true,' .
                                        '"isValidationEnabled": true' .
                                    '}',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'int_pay_profile_bambora',
            'stt_key_value'     => null,
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'int_pay_profile_authorize',
            'stt_key_value'     => null,
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'int_pay_default_card_id_bambora',
            'stt_key_value'     => null,
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'int_pay_default_card_id_authorize',
            'stt_key_value'     => null,
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'arbostar_email',
            'stt_key_value'     => 'info@arbostar.com',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'arbostar_email_name',
            'stt_key_value'     => 'Arbostar',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'arbostar_company_name',
            'stt_key_value'     => 'Arbostar',
            'stt_key_validate'  => NULL,
            'stt_section'       => null,
            'stt_label'         => null,
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_payments');
        DB::table('settings')->where('stt_key_name', '=', 'int_pay_driver')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'payment_internal_bambora')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'payment_internal_authorize')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'int_pay_profile_bambora')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'int_pay_profile_authorize')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'int_pay_default_card_id_bambora')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'int_pay_default_card_id_authorize')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'arbostar_email')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'arbostar_email_name')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'arbostar_company_name')->delete();

    }
}
