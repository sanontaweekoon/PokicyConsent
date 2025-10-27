<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolicySystem extends Migration
{
    public function up()
    {
        // --- Base ---
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->string('name_th')->nullable();
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('job_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('org_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies');
            // self-FK: หลีกเลี่ยง cascade เพื่อกัน loop/cycle บน SQL Server
            $table->foreignId('parent_id')->nullable()
                ->constrained('org_units')
                ->onDelete('no action')->onUpdate('no action');
            $table->string('code')->nullable();
            $table->string('name_th')->nullable();
            $table->string('name_en')->nullable();
            $table->string('path_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->index(['company_id', 'parent_id']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('org_unit_id')->nullable()->constrained('org_units');
            $table->foreignId('job_level_id')->nullable()->constrained('job_levels');
            $table->string('employee_no')->nullable();
            $table->string('name_th')->nullable();
            $table->string('name_en')->nullable();
            $table->string('email')->unique();
            $table->uuid('add_object_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies');
            $table->string('code')->nullable();
            $table->string('name');
            $table->boolean('is_dynamic')->default(false);
            $table->uuid('external_id')->nullable();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['group_id', 'user_id']);
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
            $table->foreignId('owner_org_unit_id')->nullable()->constrained('org_units');
            $table->boolean('is_required_ack')->default(true);
            $table->enum('status', ['draft', 'active', 'scheduled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('policy_windows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->unsignedInteger('window_no')->default(1);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_open')->default(false);
            $table->boolean('allow_late_ack')->default(false);
            $table->foreignId('create_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['policy_id', 'is_open', 'start_at', 'end_at']);
        });

        // --- Targets / Resolved recipients ---
        Schema::create('policy_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->enum('target_type', ['user', 'org_unit', 'job_level', 'group', 'company']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_descendants')->default(true);
            $table->boolean('required')->default(true);
            $table->enum('index', ['policy_window_id', 'target_type', 'target_id'])->nullable();
            $table->timestamps();
            $table->index(['policy_window_id', 'target_type', 'target_id']);
        });

        Schema::create('policy_target_resolved', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->string('employee_code', 50)->index();
            $table->string('reason')->nullable();
            $table->boolean('locked')->default(false);
            $table->string('uniqid')->nullable();
            $table->timestamps();
            $table->unique(['policy_window_id', 'employee_code']);
            $table->index(['employee_code', 'policy_window_id']);
        });

        // --- Channels & Announcements ---
        Schema::create('channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->index('is_active');
        });

        Schema::create('policy_announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels');
            $table->string('subject')->nullable();
            $table->longText('content_html')->nullable();
            $table->text('content_text')->nullable();
            $table->string('sender_name')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->enum('status', ['draft', 'queued', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamps();
            $table->index(['policy_window_id', 'channel_id', 'status']);
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

        // --- Acknowledgements (document-level) ---
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
    }

    public function down()
    {
        Schema::dropIfExists('acknowledgements');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('job_levels');
        Schema::dropIfExists('org_units');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('policy_announcements');
        Schema::dropIfExists('policy_announcement_logs');
        Schema::dropIfExists('policy_categories');
        Schema::dropIfExists('policy_target_resolved');
        Schema::dropIfExists('policy_targets');
        Schema::dropIfExists('policy_windows');
        Schema::dropIfExists('users');
    }
}
