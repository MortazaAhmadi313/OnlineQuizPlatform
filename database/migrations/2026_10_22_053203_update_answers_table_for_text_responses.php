<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('option_id')->nullable()->change();

            $table->longText('answer_text')->nullable()->after('option_id');

            $table->unsignedInteger('awarded_points')->nullable()->after('answer_text');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['answer_text', 'awarded_points']);

            $table->foreignId('option_id')->nullable(false)->change();
        });
    }
};
