<?php

use App\Models\Holiday;

function convertBRL($number){
    return number_format($number, 2, ',', '.');
}

function checkCustomerValueService($customer){
    $total_price = 0;
    foreach($customer->services as $service){
        $total_price += number_format($service->price, 2, '.', '');
    }
    return $total_price;
}


 function checkWeekend($due_date, $holiday = false) {
    $weekend = date('N', strtotime($due_date));

    switch ($due_date) {
        //Feriado e sábado ou somente sábado, retira 1 dia
        case $holiday && $weekend == 6 || $weekend == 6:
            $date = date('Y-m-d',(strtotime ('-1 day' , strtotime ( $due_date))));
        break;
       
        // Feriado e domingo ou domingo, adiciona 1 dia
        case $holiday && $weekend == 7 || $weekend == 7:
            $date = date('Y-m-d',(strtotime ('+1 day' , strtotime ( $due_date))));
        break;
        
        case $holiday == true:
            // Feriado, remove 1 dia
            $date = date('Y-m-d',(strtotime ('-1 day' , strtotime ( $due_date))));
        break;

        default:
          $date = $due_date;
        break;
    }
        
    return $date;
}

function removeAcento($string){
    return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(Ç)/"), explode(" ", "a A e E i I o O u U n N c C"), $string);
}

function checkDoliday(){
   return Holiday::where('date_holiday', date('d-m'))->first();
}

function getCityCodeNfe($city){
    // Remove o acento se tiver na cidade, transforma tudo em minúscula e pesquisa no array qual o código da cidade na receita federal
    $remove_acento = removeAcento($city);
    $lower = strtolower($remove_acento);

    $city = [
        '7523' => 'corbelia',
        '7493' => 'cascavel',
        '7935' => 'umuarama',
        '7535' => 'curitiba',
        '7563' => 'foz do iguacu',
        '2499' => 'palmares',
    ];

    return  array_search($lower, $city, true);
}

function formatCpfCnpj($value) {
    $cpf_lenght = 11;
    $cnpj_cpf = preg_replace("/\D/", '', $value);
    if (strlen($cnpj_cpf) === $cpf_lenght) {
      return preg_replace("/^(\d{3})(\d{3})(\d{3})(\d{2})$/", "$1.$2.$3-$4", $cnpj_cpf);
    } 
    return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", $cnpj_cpf);
}