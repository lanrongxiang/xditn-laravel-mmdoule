<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('cms_posts', function (Blueprint $table) {
            $table->string('cover', 2000)->change();

            $table->string('seo_title')->nullable()->comment('seo标题')->after('comment_count');
            $table->string('seo_keywords')->nullable()->comment('seo关键字')->after('seo_title');
            $table->string('seo_description')->nullable()->comment('seo描述')->after('seo_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
