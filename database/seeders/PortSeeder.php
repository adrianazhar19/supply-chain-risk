<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Port;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        Port::truncate();

        $csv = database_path('seeders/data/ports_with_country.csv');

        if (!file_exists($csv)) {
            $this->command->error("File tidak ditemukan: ".$csv);
            return;
        }

        $handle = fopen($csv, 'r');

        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            $data = array_combine($header, $row);

            if (!$data) {
                continue;
            }

            // Nama negara dari Natural Earth
            $countryName = trim($data['SOVEREIGNT']);

            $country = Country::where('name', $countryName)->first();

            if (!$country) {
                continue;
            }

            Port::create([

                'country_id'  => $country->id,

                'name'        => trim($data['name']),

                'latitude'    => (float)$data['Y'],

                'longitude'   => (float)$data['X'],

                'harbor_size' => null,

                'harbor_type' => trim($data['featurecla'])

            ]);
        }

        fclose($handle);

        $this->command->info('Port berhasil diimport : '.Port::count());
    }
}