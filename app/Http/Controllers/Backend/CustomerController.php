<?php

namespace App\Http\Controllers\Backend;

use App\Services\InterService;
use App\Http\Controllers\Controller;
use App\Interfaces\ServiceInterface;
use App\Interfaces\CustomerInterface;
use App\Http\Requests\CustomerRequest;
use App\Interfaces\CustomerNfeTypeInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(CustomerInterface $customer, ServiceInterface $serviceProvided, InterService $interService, CustomerNfeTypeInterface $customerNfeType)
    {
        $this->customer = $customer;
        $this->serviceProvided = $serviceProvided;
        $this->interService = $interService;
        $this->customerNfeType = $customerNfeType;
    }

    public function index()
    {
        $records = $this->customer->customerWithPaginate('services');
        return view('Backend.Customer.index', compact('records'));
    }

    public function login(){
        $this->interService->generatePaymentTicket();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = $this->serviceProvided->get(true);
        $nfeTypes = $this->customerNfeType->get();
        return view('Backend.Customer.create', compact('services', 'nfeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request)
    {
        $this->customer->store($request->all());
        return redirect()->route('backend.customer.index')
            ->with(['message' => 'Cliente adicionado com sucesso!', 'alert-type' => 'success']);
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
        $customer = $this->customer->find($id);
        $services = $this->serviceProvided->get(true);
        $states = $this->states();
        $nfeTypes = $this->customerNfeType->get();

        foreach($customer->services as $serviceSelected){
            $selectedService[] = $serviceSelected->id;
        }

        return view('Backend.Customer.edit', compact('customer', 'states', 'selectedService', 'services', 'nfeTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerRequest $request, $id)
    {
        $this->customer->update($request->all(), $id);
        return redirect()->route('backend.customer.index')
            ->with(['message' => 'Cliente atualizado com sucesso!', 'alert-type' => 'success']);
    }

    public function search(Request $request){
        $records = $this->customer->search($request->search, $request->type_search);
        return view('Backend.Customer.index', compact('records'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function states(){
        return [
            'AC',
            'AL',
            'AP',
            'AM',
            'BA',
            'CE',
            'DF',
            'ES',
            'GO',
            'MA',
            'MS',
            'MT',
            'MG',
            'PA',
            'PB',
            'PR',
            'PE',
            'PI',
            'RJ',
            'RN',
            'RS',
            'RO',
            'RR',
            'SC',
            'SP',
            'SE',
            'TO'
        ];
    }
}
