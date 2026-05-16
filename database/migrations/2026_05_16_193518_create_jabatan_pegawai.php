<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jabatan_pegawai', function (Blueprint $table) {
            $table->id('id_jabatan_pegawai');
            $table->string('nip', 30);
            $table->string('golongan', 10)->nullable();
            $table->string('eselon', 10)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('tempat_tugas', 100)->nullable();
            $table->string('unit_kerja', 100)->nullable();
            $table->timestamps();

            $table->foreign('nip')
                ->references('nip')
                ->on('pegawai')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
