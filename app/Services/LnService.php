<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;

class LnService
{
    public function get(String $url = '', Array $headers = [], Array $options = []){
        return Http::withHeaders($headers)->withOptions($options)->get($url);
    }

    public function post(String $url = '', Array $headers = [], Array $data = [], Array $options = [], Bool $enableForm = false){
        if($enableForm){
            return  Http::asForm()->withHeaders($headers)->withOptions($options)->post($url, $data);
        }

        return Http::withHeaders($headers)->withOptions($options)
        ->post($url,$data);
    }
    
    public function put(String $url = '', Array $headers = [], Array $data = [], Array $options = []){
        return Http::withHeaders($headers)->withOptions($options)
        ->put($url,$data);
    }

    public function delete(String $url = '', Array $headers = [], Array $options = []){
        return Http::withHeaders($headers)->withOptions($options)->delete($url);
    }

    public function postNfe($url, $archive){
        $cfile = curl_file_create($archive, 'text/xml', 'arquivo');
        $xmldata = array('xml' => $cfile);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt_array($curl, array(
            CURLOPT_POST => true, 
            CURLOPT_HEADER => false, 
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_CUSTOMREQUEST => 'POST'
            )
        );
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Basic " . env('AUTHORIZATION_NFE'),
            "Content-Type: multipart/form-data"
            )
        );
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
        
        if(curl_errno($curl)){
             return curl_errno($curl) . "<br>"; curl_error($curl);
        }else {
            $response = curl_exec($curl);
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
            $cookies = array();
            
            foreach($matches[1] as $item) {
                parse_str($item, $cookie);
                $cookies = array_merge($cookies, $cookie);
                // echo $cookies['PHPSESSID'];
            }
            curl_close($curl);
           return $response;
        }
    }
}
