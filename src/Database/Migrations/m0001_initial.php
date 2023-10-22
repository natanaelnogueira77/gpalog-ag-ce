<?php 

namespace Src\Database\Migrations;

use GTG\MVC\DB\Migration;

class m0001_initial extends Migration 
{
    public function up(): void
    {
        $this->db->createTable('conferencia', function ($table) {
            $table->id();
            $table->integer('ope_id');
            $table->integer('adm_usu_id');
            $table->integer('start_usu_id')->nullable();
            $table->dateTime('date_start')->nullable();
            $table->integer('end_usu_id')->nullable();
            $table->dateTime('date_end')->nullable();
            $table->integer('c_status');
            $table->timestamps();
        });

        $this->db->createTable('config', function ($table) {
            $table->id();
            $table->string('meta', 50);
            $table->text('value')->nullable();
        });

        $this->db->createTable('entrada', function ($table) {
            $table->id();
            $table->integer('con_id');
            $table->integer('usu_id');
            $table->integer('pro_id');
            $table->integer('package');
            $table->integer('boxes_amount');
            $table->integer('pallets_amount');
            $table->integer('units_amount');
            $table->integer('service_type');
            $table->float('pallet_height', 10, 2);
            $table->string('barcode', 50);
            $table->timestamps();
        });

        $this->db->createTable('fornecedor', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->string('name', 100);
            $table->timestamps();
        });

        $this->db->createTable('operacao', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->integer('for_id');
            $table->string('loading_password', 20);
            $table->string('ga_password', 20);
            $table->string('order_number', 20);
            $table->string('invoice_number', 20);
            $table->string('plate', 20);
            $table->tinyInteger('has_palletization');
            $table->tinyInteger('has_rework');
            $table->tinyInteger('has_storage');
            $table->tinyInteger('has_import');
            $table->tinyInteger('has_tr');
            $table->timestamps();
        });

        $this->db->createTable('pallet', function ($table) {
            $table->id();
            $table->integer('con_id');
            $table->integer('pro_id');
            $table->integer('store_usu_id');
            $table->integer('package');
            $table->integer('start_boxes_amount');
            $table->integer('boxes_amount');
            $table->integer('start_units_amount');
            $table->integer('units_amount');
            $table->integer('service_type');
            $table->float('pallet_height', 10, 2);
            $table->integer('street_number');
            $table->integer('position');
            $table->integer('height');
            $table->string('code', 20);
            $table->integer('release_usu_id')->nullable();
            $table->dateTime('release_date')->nullable();
            $table->integer('p_status');
            $table->timestamps();
        });

        $this->db->createTable('pallet_depara', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->integer('amount');
            $table->integer('a_type');
            $table->integer('to_pal_id')->nullable();
            $table->integer('from_pal_id')->nullable();
            $table->timestamps();
        });

        $this->db->createTable('produto', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('prov_id');
            $table->string('prov_name', 100);
            $table->integer('prod_id');
            $table->integer('emb_fb')->nullable();
            $table->string('ean', 20)->nullable();
            $table->string('dun14', 20)->nullable();
            $table->integer('p_length')->nullable();
            $table->integer('p_width')->nullable();
            $table->integer('p_height')->nullable();
            $table->integer('p_base')->nullable();
            $table->float('p_weight', 10, 2)->nullable();
            $table->string('plu', 20)->nullable();
            $table->timestamps();
        });

        $this->db->createTable('rua', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->integer('street_number');
            $table->integer('start_position')->nullable();
            $table->integer('end_position')->nullable();
            $table->integer('max_height')->nullable();
            $table->float('profile', 10, 2)->nullable();
            $table->integer('max_pallets')->nullable();
            $table->string('obs', 500)->nullable();
            $table->tinyInteger('is_limitless')->default('FALSE');
            $table->timestamps();
        });

        $this->db->createTable('separacao', function ($table) {
            $table->id();
            $table->integer('adm_usu_id');
            $table->integer('loading_usu_id')->nullable();
            $table->string('plate', 20)->nullable();
            $table->string('dock', 20)->nullable();
            $table->integer('s_status');
            $table->timestamps();
        });

        $this->db->createTable('separacao_item', function ($table) {
            $table->id();
            $table->integer('adm_usu_id');
            $table->integer('pro_id');
            $table->integer('a_type');
            $table->integer('amount');
            $table->integer('sep_id')->nullable();
            $table->integer('separation_usu_id')->nullable();
            $table->string('address', 50)->nullable();
            $table->integer('separation_amount')->nullable();
            $table->string('dispatch_dock', 20)->nullable();
            $table->integer('conf_usu_id')->nullable();
            $table->integer('conf_amount')->nullable();
            $table->integer('s_status');
            $table->timestamps();
        });
        
        $this->db->createTable('separacao_item_pallet', function ($table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('pal_id');
            $table->timestamps();
        });

        $this->db->createTable('social_usuario', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->string('social_id', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('social', 100)->nullable();
            $table->timestamps();
        });

        $this->db->createTable('usuario', function ($table) {
            $table->id();
            $table->integer('utip_id');
            $table->string('name', 50);
            $table->string('email', 100);
            $table->string('password', 100);
            $table->string('token', 100);
            $table->string('slug', 100);
            $table->timestamps();
        });

        $this->db->createTable('usuario_meta', function ($table) {
            $table->id();
            $table->integer('usu_id');
            $table->string('meta', 50);
            $table->text('value')->nullable();
        });

        $this->db->createTable('usuario_tipo', function ($table) {
            $table->id();
            $table->string('name_sing', 50);
            $table->string('name_plur', 50);
            $table->timestamps();
        });

        $this->db->createProcedure('SP_UpdatePalletStock', function ($procedure) {
            $procedure->integer('id_pallet');
            $procedure->integer('amount_add');
            $procedure->integer('amount_type');
            $procedure->statement("
                IF amount_type = 1 THEN 
                    UPDATE pallet 
                    SET boxes_amount = boxes_amount + amount_add, 
                        units_amount = units_amount + amount_add * package 
                    WHERE id = id_pallet;
                ELSEIF amount_type = 2 THEN 
                    UPDATE pallet 
                    SET boxes_amount = FLOOR((units_amount + amount_add) / package), 
                        units_amount = units_amount + amount_add
                    WHERE id = id_pallet;
                END IF;
            ");
        });

        $this->db->createTrigger('TRG_PalletFromTo_AI', function ($trigger) {
            $trigger->event('AFTER INSERT ON `pallet_depara` FOR EACH ROW');
            $trigger->statement("
                CALL SP_UpdatePalletStock(new.to_pal_id, new.amount, new.a_type);
                CALL SP_UpdatePalletStock(new.from_pal_id, new.amount * -1, new.a_type);
            ");
        });

        $this->db->createTrigger('TRG_PalletFromTo_AU', function ($trigger) {
            $trigger->event('AFTER UPDATE ON `pallet_depara` FOR EACH ROW');
            $trigger->statement("
                IF old.a_type = new.a_type THEN 
                    CALL SP_UpdatePalletStock(new.to_pal_id, new.amount - old.amount, new.a_type);
                    CALL SP_UpdatePalletStock(new.from_pal_id, old.amount - new.amount, new.a_type);
                ELSE 
                    CALL SP_UpdatePalletStock(new.from_pal_id, old.amount * -1, old.a_type);
                    CALL SP_UpdatePalletStock(new.from_pal_id, new.amount, new.a_type);
                END IF;
            ");
        });
        
        $this->db->createTrigger('TRG_PalletFromTo_AD', function ($trigger) {
            $trigger->event('AFTER DELETE ON `pallet_depara` FOR EACH ROW');
            $trigger->statement("
                CALL SP_UpdatePalletStock(old.to_pal_id, old.amount * -1, old.a_type);
                CALL SP_UpdatePalletStock(old.from_pal_id, old.amount, old.a_type);
            ");
        });
    }

    public function down(): void
    {
        $this->db->dropTriggerIfExists('TRG_PalletFromTo_AI');
        $this->db->dropTriggerIfExists('TRG_PalletFromTo_AU');
        $this->db->dropTriggerIfExists('TRG_PalletFromTo_AD');
        $this->db->dropProcedureIfExists('SP_UpdatePalletStock');
        $this->db->dropTableIfExists('conferencia');
        $this->db->dropTableIfExists('config');
        $this->db->dropTableIfExists('entrada');
        $this->db->dropTableIfExists('fornecedor');
        $this->db->dropTableIfExists('operacao');
        $this->db->dropTableIfExists('pallet');
        $this->db->dropTableIfExists('pallet_depara');
        $this->db->dropTableIfExists('produto');
        $this->db->dropTableIfExists('rua');
        $this->db->dropTableIfExists('separacao');
        $this->db->dropTableIfExists('separacao_item');
        $this->db->dropTableIfExists('separacao_item_pallet');
        $this->db->dropTableIfExists('social_usuario');
        $this->db->dropTableIfExists('usuario');
        $this->db->dropTableIfExists('usuario_meta');
        $this->db->dropTableIfExists('usuario_tipo');
    }
}