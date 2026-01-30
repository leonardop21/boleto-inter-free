<?php

namespace App\Repositories;

use App\Interfaces\CustomerNfeTypeInterface;
use App\Models\Customer;
use App\Models\CustomerNfeType;

class CustomerNfeTypeRepository extends AbstractRepository implements CustomerNfeTypeInterface
{
    protected $model = CustomerNfeType::class;

    public function firstOrCreate($data){
        $custNfe = new CustomerNfeType();
        $dados = array_key_exists('query', $data) ? array_diff_key($data, ['query' => '']) : $data;
       return $custNfe->firstOrCreate($dados);
    }

    public function edit($id){
    }

    public function destroy($id){
        $find = CustomerNfeType::where('id', $id)->firstOrFail();
        return $find->delete();
    }
}
