<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignaturePayloadToAcknowledgements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acknowledgements', function (Blueprint $table) {
            if (!Schema::hasColumn('acknowledgements', 'signature_payload')) {
                $table->json('signature_payload')->nullable()->after('signer_name');
            }
            if (!Schema::hasColumn('acknowledgements', 'signature_hash')) {
                $table->string('signature_hash', 128)->nullable()->after('signature_payload');
            }
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
            if (Schema::hasColumn('acknowledgements', 'signature_payload')) $table->dropColumn('signature_payload');
            if (Schema::hasColumn('acknowledgements', 'signature_hash')) $table->dropColumn('signature_hash');
        });
    }
}
