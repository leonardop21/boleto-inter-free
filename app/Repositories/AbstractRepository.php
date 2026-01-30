<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Cache;

abstract class AbstractRepository
{
    protected $model;
    protected $tableCache;
    protected $limit_page;

    public function __construct()
    {   // Intância o model
        $this->model = $this->resolveModel();
        // Guarda o nome da tabela
        $this->tableCache = $this->model->getTable();
        $this->limit_page = env('PAGINATION_LIMIT');
        $this->time_cache = env('TIME_CACHE_IN_SECONDS');

    }

    public function get($active = false){
        if($active){
            if(!Cache::tags($this->tableCache)->has('_get_active')){
                $records = $this->model::where('status', '1')->orderBy('id', 'desc')->get();
                Cache::tags([$this->tableCache])->put('_get_active', $records, $this->time_cache);
            }
            return Cache::tags([$this->tableCache])->get('_get_active');
        }else {
            if(!Cache::tags($this->tableCache)->has('_get')){
                $records = $this->model::orderBy('id', 'desc')->get();
                Cache::tags([$this->tableCache])->put('_get', $records, $this->time_cache);
            }
            return Cache::tags([$this->tableCache])->get('_get');
        }
        
    }

    public function store($data){
        $dados = array_key_exists('query', $data) ? array_diff_key($data, ['query' => '']) : $data;
       return $this->model::create($dados);
    }
    public function update($data, $id){
        $find = $this->model::find($id);
        $dados = array_key_exists('query', $data) ? array_diff_key($data, ['query' => '']) : $data;
        $find->update($dados);
        return $find;
    }

    public function first()
    {
        if(!Cache::tags($this->tableCache)->has('_first')){
            $records = $this->model::first();
            Cache::tags([$this->tableCache])->put('_first', $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_first');
    }
    
    public function find($id)
    {
        if(!Cache::tags($this->tableCache)->has('_record_id_' . $id)){
            $records = $this->model::find($id);
            Cache::tags([$this->tableCache])->put('_record_id_' . $id, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_record_id_' . $id);
    }
    
    public function findByUuid($uuid)
    {
        if(!Cache::tags($this->tableCache)->has('_record_uuid_' . $uuid)){
            $records = $this->model::where('uuid', $uuid)->first();
            Cache::tags([$this->tableCache])->put('_record_uuid_' . $uuid, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_record_uuid_' . $uuid);
    }

    public function paginate() {
        $page = request()->get('page', 1);
        
        if(!Cache::tags($this->tableCache)->has('_page_' . $page)){
            $records = $this->model::orderBy('id', 'desc')->paginate($this->limit_page);
            Cache::tags([$this->tableCache])->put('_page_' . $page, $records, $this->time_cache);
        }
        return Cache::tags([$this->tableCache])->get('_page_' . $page);
    }

    public function forgetCacheTag(){
       Cache::tags($this->tableCache)->flush();
    }
    public function search($search, $field){
        return $this->model::where($field, 'Like', '%' . $search . '%')->orderBy('id', 'desc')->paginate(10);
    }

    public function delete($id){
        $find = $this->model::where('id', $id)->first();
        $find->delete($find);
        return;
    }

    public function resolveModel(){
        // Identifica qual o Model e atribui na variável
        return app($this->model);
    }
}