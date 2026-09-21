<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per user — updated_at doubles as "last typed at". Polled by
     * other participants (poll() in InspectorMessageController) and treated
     * as stale after a few seconds, so we never need an explicit
     * "stopped typing" event from the client.
     */
    public function up(): void
    {
        Schema::create('work_note_typings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->string('role');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_note_typings');
    }
};
