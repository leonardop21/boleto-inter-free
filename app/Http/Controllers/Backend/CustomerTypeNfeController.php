<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\CustomerNfeTypeInterface;
use App\Http\Requests\StoreCustomerTypeNfeRequest;
use App\Http\Responses\ApiResponse;

class CustomerTypeNfeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(CustomerNfeTypeInterface $customerNfeType)
    {
        $this->customerNfeType = $customerNfeType;
    }
    public function index()
    {
        $records = $this->customerNfeType->paginate();
        return view('Backend.Nfe.type-note-index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Backend.Nfe.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerTypeNfeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerTypeNfeRequest $request)
    {
        $this->customerNfeType->firstOrCreate($request->validated());

        return redirect()->route('backend.nfe.indexTypeNote')
            ->with(['message' => 'Tipo de nota criado com sucesso!', 'alert-type' => 'success']);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->customerNfeType->destroy($id);
        
        return redirect()->route('backend.nfe.indexTypeNote')
            ->with(['message' => 'Tipo de nota removido com sucesso!', 'alert-type' => 'success']);
    }
}
