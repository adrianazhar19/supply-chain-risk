<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;


class CountrySeeder extends Seeder
{
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Country::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');


        $countries = countries();


        foreach($countries as $item){


            Country::create([

                'name' => $item['name'],

                'code' => $item['iso_3166_1_alpha2'],

                'currency' => 
                    $item['currency']['iso_4217_code']
                    ?? '-',

                'region' =>
                    $item['geo']['continent']
                    ?? '-',

            ]);

        }


        echo "\nTOTAL COUNTRY : "
        . Country::count()
        . "\n";

    }
}