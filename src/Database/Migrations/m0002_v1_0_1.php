<?php 

namespace Src\Database\Migrations;

use GTG\MVC\DB\Migration;

class m0002_v1_0_1 extends Migration 
{
    public function up(): void
    {
        $this->db->alterTable('entrada', function ($table) {
            $table->date('expiration_date')->nullable();
        });

        $this->db->alterTable('pallet', function ($table) {
            $table->integer('sep_id')->nullable();
            $table->date('expiration_date')->nullable();
        });

        $this->db->alterTable('separacao', function ($table) {
            $table->dateTime('loading_date')->nullable();
        });

        $this->db->alterTable('separacao_item', function ($table) {
            $table->integer('pal_id');
            $table->dateTime('separation_date')->nullable();
            $table->dateTime('conf_date')->nullable();
            $table->string('order_number', 20)->nullable();
        });
    }

    public function down(): void
    {
        $this->db->alterTable('entrada', function ($table) {
            $table->dropColumn('expiration_date');
        });

        $this->db->alterTable('pallet', function ($table) {
            $table->dropColumn('sep_id');
            $table->dropColumn('expiration_date');
        });

        $this->db->alterTable('separacao', function ($table) {
            $table->dropColumn('loading_date');
        });

        $this->db->alterTable('separacao_item', function ($table) {
            $table->dropColumn('pal_id');
            $table->dropColumn('separation_date');
            $table->dropColumn('conf_date');
            $table->dropColumn('order_number');
        });
    }
}