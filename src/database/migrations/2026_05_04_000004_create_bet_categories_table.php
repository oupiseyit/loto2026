<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->tinyInteger('type')->unsigned()->comment('1=P, 2=LO');
            $table->boolean('status')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_categories');
    }
};
