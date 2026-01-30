<?php 

namespace App\Repositories;

use App\Interfaces\HolidayInterface;
use App\Models\Holiday;

class HolidayRepository extends AbstractRepository implements HolidayInterface {
    
    protected $model = Holiday::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function find($data){
       return Holiday::where('date_holiday', $data)->first();
    }

    public function edit($id){
        return Holiday::where('id', $id)->firstOrFail();
    }

    public function paginate(){
        return Holiday::orderBy('id', 'desc')->paginate(10);
    }

}