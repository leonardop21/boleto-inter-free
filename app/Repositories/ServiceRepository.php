<?php

namespace App\Repositories;

use App\Interfaces\ServiceInterface;
use App\Models\Service;

class ServiceRepository extends AbstractRepository implements ServiceInterface
{
    protected $model = Service::class;
    protected $abs = AbstractRepository::class;

    public function store($data)
    {   
        $data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $data['price'])));
        return $this->abs::store($data);
    }

    public function edit($id){
    }

    public function update($data, $id)
    {
        $data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $data['price'])));
        return $this->abs::update($data, $id);
    }

    public function search($search, $field)
    {
        return $this->abs::search($search, 'name');
    }

    public function destroy($id)
    {

    }
}
