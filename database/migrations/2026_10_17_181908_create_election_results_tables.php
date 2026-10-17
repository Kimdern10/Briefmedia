<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Election results board shown inside a story:
 * - elections: one board per post (title, what the areas are called, counting or final)
 * - election_candidates: each candidate / party with its colour
 * - election_areas: each area results come from (e.g. Anambra's 21 LGAs) and whether it has reported
 * - election_votes: votes per candidate per area; totals are the sum over areas
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('elections')) {
            Schema::create('elections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id')->unique();
                $table->string('title', 160);
                $table->string('area_label', 40)->default('LGA');
                $table->boolean('is_final')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('election_candidates')) {
            Schema::create('election_candidates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('election_id')->index();
                $table->string('name', 120);
                $table->string('party', 30)->nullable();
                $table->string('color', 7)->default('#e4407f');
                $table->unsignedTinyInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('election_areas')) {
            Schema::create('election_areas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('election_id')->index();
                $table->string('name', 120);
                $table->boolean('reported')->default(false);
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('election_votes')) {
            Schema::create('election_votes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('election_area_id');
                $table->unsignedBigInteger('election_candidate_id')->index();
                $table->unsignedInteger('votes')->default(0);
                $table->timestamps();
                $table->unique(['election_area_id', 'election_candidate_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('election_votes');
        Schema::dropIfExists('election_areas');
        Schema::dropIfExists('election_candidates');
        Schema::dropIfExists('elections');
    }
};
