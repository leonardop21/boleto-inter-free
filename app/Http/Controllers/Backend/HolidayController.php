<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\HolidayInterface;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Http\Responses\ApiResponse;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
     public function __construct(HolidayInterface $holiday)
     {
        $this->holiday = $holiday;
     }
    public function index()
    {
        $records = $this->holiday->paginate();
        return view('Backend.Holiday.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Backend.Holiday.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreHolidayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHolidayRequest $request)
    {
        $this->holiday->store($request->validated());

        return redirect()->route('backend.feriado.index')
            ->with(['message' => 'Feriado adicionado com sucesso!', 'alert-type' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
       $record = $this->holiday->edit($id);
        return view('Backend.Holiday.edit', compact('record'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateHolidayRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHolidayRequest $request, $id)
    {
        $this->holiday->update($request->validated(), $id);
        return redirect()->route('backend.feriado.index')
            ->with(['message' => 'Feriado editado com sucesso!', 'alert-type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->holiday->delete($id);
        return redirect()->route('backend.feriado.index')
            ->with(['message' => 'Feriado deletado com sucesso!', 'alert-type' => 'success']);
    }
}
