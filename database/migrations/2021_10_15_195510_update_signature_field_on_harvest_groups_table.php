<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\HarvestGroup;
class UpdateSignatureFieldOnHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {

            $harvests = HarvestGroup::where('signature', '!=', '')->get();
            foreach($harvests as $harvest)
            {
                $png_url = microtime(true).".png";
                $path = public_path()."/uploads/" . $png_url;
                Image::make(file_get_contents($harvest->signature))->save($path);
                $harvest->signature = $png_url;
                $harvest->update();
            }
            $table->string('signature')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
