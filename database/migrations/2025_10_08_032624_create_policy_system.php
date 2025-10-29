<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolicySystem extends Migration
{
    public function up()
    {
        // --- Base ---
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('recipient_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['department', 'level', 'custom'])->default('custom');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // --- Policies ---
        Schema::create('policy_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->string('name');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('category_id')->constrained('policy_categories');
            $table->string('code')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users');
            $table->boolean('is_required_ack')->default(true);
            $table->enum('status', ['draft', 'active', 'scheduled'])->default('draft');
            $table->enum('recipient_type', ['all', 'target'])->default('all');
            $table->json('recipient_emails')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('policy_windows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->string('window_no', 50)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_open')->default(false);
            $table->boolean('allow_late_ack')->default(false);
            $table->foreignId('create_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['policy_id', 'is_open', 'start_at', 'end_at']);
        });


        Schema::create('policy_announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->longText('content_html')->nullable();
            $table->text('content_text')->nullable();
            $table->string('sender_name')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->enum('status', ['draft', 'queued', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamps();
            $table->index(['policy_window_id', 'status']);
        });

        Schema::create('policy_announcement_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('announcement_id')->constrained('policy_announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'queued', 'sending', 'sent', 'failed'])->default('draft');
            $table->json('meta')->nullable();
            $table->enum('index', ['announcement_id', 'user_id', 'status'])->nullable();
            $table->timestamps();
            $table->index(['announcement_id', 'user_id', 'status']);
        });

        // --- Acknowledgements ---
        Schema::create('acknowledgements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')
                ->constrained('policy_windows')
                ->cascadeOnDelete();
            $table->string('employee_code', 50)->index();
            $table->enum('status', ['pending', 'acknowledged'])->default('pending');
            $table->string('signer_name')->nullable();
            $table->json('signature_payload')->nullable();
            $table->string('signature_hash')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->unique(['policy_window_id', 'employee_code']);
        });

         // --- recipient ---
        Schema::create('recipient_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_group_id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('recipient_group_id')
                ->references('id')
                ->on('recipient_groups')
                ->onDelete('cascade');

            $table->unique(['recipient_group_id', 'email']);
        });

        Schema::create('policy_recipient_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('policy_id');
            $table->unsignedBigInteger('recipient_group_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('policy_id')
                ->references('id')
                ->on('policies')
                ->onDelete('cascade');

            $table->foreign('recipient_group_id')
                ->references('id')
                ->on('recipient_groups')
                ->onDelete('cascade');

            $table->unique(['policy_id', 'recipient_group_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_recipient_groups');
        Schema::dropIfExists('recipient_group_members');
        Schema::dropIfExists('acknowledgements');
        Schema::dropIfExists('policy_announcement_logs');
        Schema::dropIfExists('policy_announcements');
        Schema::dropIfExists('policy_windows');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('policy_categories');
        Schema::dropIfExists('recipient_groups');
        Schema::dropIfExists('users');
    }
}
