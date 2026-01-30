<?php

namespace App\Interfaces;

interface HolidayInterface{
    public function find($id);
    public function edit($id);
    public function paginate();
}