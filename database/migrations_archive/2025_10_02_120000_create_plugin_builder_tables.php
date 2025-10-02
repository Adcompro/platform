<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plugin code repository
        Schema::create('plugin_code', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('file_name'); // e.g., 'SlackIntegration.php'
            $table->string('file_type')->default('php'); // php, js, blade, css
            $table->longText('code'); // Actual code content
            $table->string('namespace')->nullable(); // App\Plugins\SlackIntegration
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['plugin_id', 'file_type']);
        });

        // Plugin hooks (event listeners)
        Schema::create('plugin_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('hook_name'); // 'project.created', 'invoice.finalized'
            $table->string('callback_method'); // 'onProjectCreated'
            $table->integer('priority')->default(10);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable(); // Hook-specific config
            $table->timestamps();

            $table->index(['hook_name', 'is_active', 'priority']);
        });

        // Plugin API endpoints (auto-generated)
        Schema::create('plugin_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // GET, POST, PUT, DELETE
            $table->string('path'); // /api/plugins/slack/notify
            $table->string('handler_method'); // 'handleNotification'
            $table->boolean('requires_auth')->default(true);
            $table->json('permissions')->nullable(); // ['projects.read']
            $table->json('rate_limit')->nullable(); // ['max' => 100, 'per' => 'minute']
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['plugin_id', 'method', 'path']);
        });

        // Plugin dependencies
        Schema::create('plugin_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('package_name'); // 'guzzlehttp/guzzle'
            $table->string('version'); // '^7.0'
            $table->enum('type', ['composer', 'npm', 'system'])->default('composer');
            $table->boolean('is_installed')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });

        // Plugin migrations (auto-run)
        Schema::create('plugin_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('migration_name'); // 'create_slack_messages_table'
            $table->longText('up_code'); // Code for up() method
            $table->longText('down_code'); // Code for down() method
            $table->boolean('is_executed')->default(false);
            $table->timestamp('executed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Plugin tests
        Schema::create('plugin_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('test_name');
            $table->longText('test_code'); // PHPUnit test code
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('result')->nullable(); // Test output
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        // Plugin marketplace (internal)
        Schema::create('plugin_marketplace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->text('documentation')->nullable();
            $table->string('category'); // 'integration', 'reporting', 'automation'
            $table->json('screenshots')->nullable();
            $table->boolean('is_public')->default(false); // Share with team
            $table->boolean('is_approved')->default(false); // Admin approval
            $table->integer('install_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->timestamps();
        });

        // Plugin installations (who has which plugin)
        Schema::create('plugin_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installed_by')->constrained('users');
            $table->json('config')->nullable(); // Company-specific config
            $table->boolean('is_active')->default(true);
            $table->timestamp('installed_at');
            $table->timestamps();

            $table->unique(['plugin_id', 'company_id']);
        });

        // Plugin execution logs
        Schema::create('plugin_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('hook_name')->nullable();
            $table->string('endpoint')->nullable();
            $table->enum('status', ['success', 'error', 'timeout'])->default('success');
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['plugin_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // Add fields to existing plugins table
        Schema::table('plugins', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_active'); // User-built plugin
            $table->foreignId('author_id')->nullable()->after('is_custom')->constrained('users');
            $table->string('sandbox_path')->nullable()->after('author_id'); // Storage path
            $table->json('metadata')->nullable()->after('sandbox_path'); // Custom fields
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_execution_logs');
        Schema::dropIfExists('plugin_installations');
        Schema::dropIfExists('plugin_marketplace');
        Schema::dropIfExists('plugin_tests');
        Schema::dropIfExists('plugin_migrations');
        Schema::dropIfExists('plugin_dependencies');
        Schema::dropIfExists('plugin_endpoints');
        Schema::dropIfExists('plugin_hooks');
        Schema::dropIfExists('plugin_code');

        Schema::table('plugins', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn(['is_custom', 'author_id', 'sandbox_path', 'metadata']);
        });
    }
};
