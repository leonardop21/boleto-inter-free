<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HolidaySeeder  extends Seeder {

    public function run(){
        $holidays = $this->holidays();

        \DB::table('holidays')->insert($holidays);

    }

    public function holidays(){
        return [
            ["date_holiday" => "1/01", "description" => "Ano novo"],
            ["date_holiday" => "20/02", "description" => "Carnaval"],
            ["date_holiday" => "21/02", "description" => "Carnaval"],
            ["date_holiday" => "7/04", "description" => "Paixão de cristo"],
            ["date_holiday" => "21/01", "description" => "Tiradentes"],
            ["date_holiday" => "1/05", "description" => "Dia do trabalho"],
            ["date_holiday" => "7/09", "description" => "Independência do Brasil"],
            ["date_holiday" => "12/10", "description" => "Nossa Senhora Aparecida - Padroeira do Brasil"],
            ["date_holiday" => "2/11", "description" => "finados"],
            ["date_holiday" => "15/11", "description" => "Proclamação da república"],
            ["date_holiday" => "25/12", "description" => "Natal"],
        ];
    }

}