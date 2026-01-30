<?php

namespace App\Interfaces;

interface CustomerInterface
{
    public function paginate();
    public function find($id);
    public function store($data);
    public function edit($id);
    public function search($search, $field);
    public function update($data, $id);
    public function destroy($id);
}
