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
            $table->unique(['group_id','user_id']);
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
            $table->enum('status', ['draft','active','archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('policy_version', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->text('change_log')->nullable();
            $table->string('file_attachment_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['draft','published','retired'])->default('draft');
            $table->enum('completion_rule', ['all','any','threshold'])->default('all');
            $table->unsignedInteger('completion_threshold')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['policy_id','version_no']);
        });

        Schema::create('policy_windows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_version_id')->constrained('policy_version')->cascadeOnDelete();
            $table->unsignedInteger('window_no')->default(1);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_open')->default(false);
            $table->boolean('allow_late_ack')->default(false);
            $table->foreignId('create_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['policy_version_id','is_open']);
        });

        Schema::create('policy_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_version_id')->constrained('policy_version')->cascadeOnDelete();
            $table->enum('rule_type', ['jsonlogic','sql','simple']);
            $table->json('rule_json')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // --- Targets / Resolved recipients ---
        Schema::create('policy_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->enum('target_type', ['user','org_nit','job_level','group','company']); // สะกดเดิม
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_descendants')->default(true);
            $table->boolean('required')->default(true);
            $table->enum('index', ['policy_window_id','target_type','target_id'])->nullable();
            $table->timestamps();
            $table->index(['policy_window_id','target_type','target_id']);
        });

        Schema::create('policy_target_resolved', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->boolean('locked')->default(false);
            $table->enum('uniqid', ['policy_window_id','user_id'])->nullable();
            $table->timestamps();
            $table->unique(['policy_window_id','user_id']);
            $table->index(['user_id','policy_window_id']);
        });

        // --- Channels & Announcements ---
        Schema::create('chanels', function (Blueprint $table) { // สะกดเดิม
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('policy_announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_window_id')->constrained('policy_windows')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('chanels');
            $table->string('subject')->nullable();
            $table->longText('content_html')->nullable();
            $table->text('content_text')->nullable();
            $table->string('sender_name')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->enum('status', ['draft','queued','sending','sent','failed'])->default('draft');
            $table->timestamps();
            $table->index(['policy_window_id','channel_id','status']);
        });

        Schema::create('policy_announcements_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('announcement_id')->constrained('policy_announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft','queued','sending','sent','failed'])->default('draft');
            $table->json('meta')->nullable();
            $table->enum('index', ['announcement_id','user_id','status'])->nullable();
            $table->timestamps();
            $table->index(['announcement_id','user_id','status']);
        });

        // --- Acknowledgements (document-level) ---
        Schema::create('acknowledgements', function (Blueprint $table) {
            $table->bigIncrements('id');

            // เส้นหลัก: ผ่าน window
            $table->foreignId('policy_window_id')
                  ->constrained('policy_windows')
                  ->cascadeOnDelete();

            // กัน multiple paths: ห้าม cascade ตรงจาก policy_version
            $table->foreignId('policy_version_id')
                  ->constrained('policy_version')
                  ->onDelete('no action')->onUpdate('no action');

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending','acknowledged'])->default('pending');
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signature_hash')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['policy_window_id','user_id']);
            $table->index(['policy_version_id','status']);
        });

        // --- Activity logs ---
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event');
            $table->nullableMorphs('subject'); // subject_type, subject_id
            $table->nullableMorphs('causer');  // causer_type, causer_id
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        // --- Policy Items (per version) ---
        Schema::create('policy_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('policy_version_id')->constrained('policy_version')->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('label');
            $table->text('help_text')->nullable();
            $table->enum('type', ['checkbox','radio','select','text','textarea','file'])->default('checkbox');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('min_checks')->nullable();
            $table->unsignedInteger('max_checks')->nullable();
            $table->boolean('requires_attachment')->default(false);
            $table->unsignedInteger('order_no')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['policy_version_id','order_no']);
        });

        Schema::create('policy_item_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            // กันไม่ให้เกิดเส้นทางผ่าน options → acknowledgements
            $table->foreignId('policy_item_id')
                  ->constrained('policy_items')
                  ->onDelete('no action')->onUpdate('no action');
            $table->string('label');
            $table->string('value')->nullable();
            $table->unsignedInteger('order_no')->default(0);
            $table->boolean('is_accept_option')->default(true);
            $table->timestamps();
            $table->index(['policy_item_id','order_no']);
        });

        Schema::create('policy_item_acknowledgements', function (Blueprint $table) {
            $table->bigIncrements('id');

            // เส้นหลัก: ผ่าน window เท่านั้น
            $table->foreignId('policy_window_id')
                  ->constrained('policy_windows')
                  ->cascadeOnDelete();

            // กัน multiple paths: ไม่ cascade จาก policy_version
            $table->foreignId('policy_version_id')
                  ->constrained('policy_version')
                  ->onDelete('no action')->onUpdate('no action');

            // กัน multiple paths: ไม่ cascade จาก policy_items
            $table->foreignId('policy_item_id')
                  ->constrained('policy_items')
                  ->onDelete('no action')->onUpdate('no action');

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // ค่าคำตอบ
            $table->boolean('checked')->default(false);

            // ยัง nullable ได้ แต่ห้าม SET NULL อัตโนมัติ (กันหลายเส้นทาง)
            $table->foreignId('option_id')->nullable()
                  ->constrained('policy_item_options')
                  ->onDelete('no action')->onUpdate('no action');

            $table->text('value_text')->nullable();
            $table->string('file_path')->nullable();

            $table->timestamp('acknowledged_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->unique(['policy_window_id','policy_item_id','user_id'], 'uq_item_ack');
            $table->index(['user_id','policy_window_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_item_acknowledgements');
        Schema::dropIfExists('policy_item_options');
        Schema::dropIfExists('policy_items');

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('acknowledgements');
        Schema::dropIfExists('policy_announcements_logs');
        Schema::dropIfExists('policy_announcements');
        Schema::dropIfExists('chanels');

        Schema::dropIfExists('policy_target_resolved');
        Schema::dropIfExists('policy_targets');
        Schema::dropIfExists('policy_conditions');
        Schema::dropIfExists('policy_windows');
        Schema::dropIfExists('policy_version');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('policy_categories');

        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');

        Schema::dropIfExists('users');
        Schema::dropIfExists('org_units');
        Schema::dropIfExists('job_levels');
        Schema::dropIfExists('companies');
    }
}
