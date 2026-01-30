<?php

namespace App\Interfaces;

interface NfeInterface
{
    public function find($id);
    public function store($data);
    public function edit($id);
    public function update($data, $id);
}
