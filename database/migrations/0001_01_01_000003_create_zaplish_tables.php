<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Force delete everything
        // TODO remove
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('settings');
        Schema::dropIfExists('relations');
        Schema::dropIfExists('media_versions');
        Schema::dropIfExists('media');
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('block_groups');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('content_types');
        Schema::enableForeignKeyConstraints();

        // Content Types
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('active')->default(true);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // Content
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('active')->default(true);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // Block Groups
        Schema::create('block_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Blocks
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('active')->default(true);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // Content Blocks
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('active')->default(true);
            $table->json('settings')->nullable();
        });

        // Media
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('uri');
            $table->string('path');
            $table->string('extension');
            $table->string('filename_original');
            $table->string('media_type');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Media Versions
        Schema::create('media_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->restrictOnDelete();
            $table->string('size_key');
            $table->string('uri');
            $table->string('path');
            $table->string('extension');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->unique(['media_id', 'size_key']);
        });

        // Unified Relations (content ↔ media, content ↔ content, block ↔ media, etc.)
        Schema::create('relations', function (Blueprint $table) {
            $table->id();
            $table->string('source_type'); // "content", "block"
            $table->unsignedBigInteger('source_id');
            $table->string('target_type'); // "media", "content"
            $table->unsignedBigInteger('target_id');
            $table->string('relation_type')->nullable(); // "image", "category"
            $table->string('field_name')->nullable();
            $table->unsignedInteger('order')->default(1);

            $table->index(['source_type', 'source_id']);
            $table->index(['target_type', 'target_id']);
            $table->index(['field_name']);
        });

        // Global Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('relations');
        Schema::dropIfExists('media_versions');
        Schema::dropIfExists('media');
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('block_groups');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('content_types');
    }
};
