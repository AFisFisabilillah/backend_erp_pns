<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alamat_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique("nip_unique");
            $table->text('alamat')->nullable();
            $table->string("kota", 50)->nullable();
            $table->string("provinsi", 50)->nullable();
            $table->timestamps();
            $table->foreign('nip')
                ->references('nip')
                ->on('pegawai')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
