<?php

namespace App\Interfaces;

interface CustomerNfeTypeInterface
{
    public function find($id);
    public function firstOrCreate($data);
    public function edit($id);
    public function update($data, $id);
}
