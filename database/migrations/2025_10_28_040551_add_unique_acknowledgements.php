<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueAcknowledgements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acknowledgements', function (Blueprint $table) {
            $table->unique(['policy_window_id', 'employee_code'], 'ack_window_employee_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acknowledgements', function (Blueprint $table) {
            $table->dropUnique('ack_window_employee_unique');
        });
    }
}
