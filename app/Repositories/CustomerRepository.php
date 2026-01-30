<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Interfaces\CustomerInterface;
use App\Models\Boleto;
use Illuminate\Support\Facades\Cache;

class CustomerRepository extends AbstractRepository implements CustomerInterface
{
    protected $model = Customer::class;
    protected $abs = AbstractRepository::class;
    
    public function customerActiveWith($with){
            $tableCache = $this->model;
            $records = Cache::remember($tableCache . '_get_active_with', $this->time_cache, function() use($with) {
                return $this->model::where('status', '1')->with($with)->has($with)->orderBy('id', 'desc')->get();
            });
        return $records;
    }
    
    public function customerActiveWithNfe($with){
        $tableCache = $this->model->getTable();

        if(!Cache::tags($tableCache)->has('_get_active_with_nfe')){
            $records = $this->model::where(
                [['status', '=', '1'], ['generate_nfe', '=', '1']])->with($with)->orderBy('id', 'desc')->get();
            Cache::tags([$tableCache])->put('_get_active_with_nfe', $records, env('TIME_CACHE_IN_SECONDS'));
        }
        return Cache::tags([$tableCache])->get('_get_active_with_nfe');
    }

    public function customerWithPaginate($with){
        $page = request()->get('page', 1);
        $tableCache = $this->model->getTable();
        
        if(!Cache::tags($tableCache)->has('_get_with_page_' . $page)){
            $records = $this->model::with($with)->orderBy('id', 'desc')->paginate(env('PAGINATION_LIMIT'));
            Cache::tags([$tableCache])->put('_get_with_page_' . $page, $records, env('TIME_CACHE_IN_SECONDS'));
        }
        return Cache::tags([$tableCache])->get('_get_with_page_' . $page);
    }
    
    public function store($data)
    {
        $type = (strlen($data['cpf_cnpj']) == 11 ? 'fisica' : 'juridica');
        $data['type_of_person'] = $type;

        $services = $data['services'];
        unset($data['services']);
        
        $customer = $this->abs::store($data);
        $customer->services()->attach($services);
    }

    public function find($id){
        return Customer::with('services')->find($id);
    }

    public function edit($id){
    }

    public function update($data, $id)
    {
        $type = (strlen($data['cpf_cnpj']) == 11 ? 'fisica' : 'juridica');
        $data['type_of_person'] = $type;

        $services = $data['services'];
        unset($data['services']);
        
        $customer = $this->abs::update($data, $id);
        $customer->services()->sync($services);
    }

    public function search($search, $field)
    {
        return $this->abs::search($search, $field);
    }

    public function destroy($id)
    {

    }
}
