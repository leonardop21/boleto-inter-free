@extends('Backend.Layouts.panel')
@section('title','Gerar boleto avulso')
@section('content')

<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Gerar boleto avulso</h3>
          </div>
          <form method="post" action="{{route('backend.fatura.boleto.store')}}">
           @csrf
            <div class="card-body">
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Cliente*</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Selecione o Cliente</option>
                            @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="description">Descrição</label>
                        <input type="text" class="form-control" name="description" id="description" value="{{old('description')}}" placeholder="Boleto referente a entrada do site abc.xyz" maxlength="255">
                    </div>
              </div>
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="my_number">Seu número* <small>(Para o seu controle interno)</small></label>
                        <input type="text" class="form-control" name="my_number" id="my_number" value="{{old('my_number')}}" placeholder="Ex: ABC-123" maxlength="255" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="price">VALOR* <small>(Não use R$)</small></label>
                        <input type="text" class="form-control" name="price" id="price" value="{{old('price')}}" placeholder="199,75" required>
                    </div>
              </div>
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="due_date">Data de vencimento</small></label>
                        <input type="date" class="form-control" name="due_date" id="due_date" value="{{old('due_date')}}" placeholder="Data de vencimento" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="date_to_pay_after_due_date">Quantos dias após o vencimento, o boleto ainda poderá ser pago?*</label>
                        <input type="number" min="0" class="form-control" name="date_to_pay_after_due_date" id="date_to_pay_after_due_date" value="{{old('date_to_pay_after_due_date')}}" placeholder="3" required>
                        <small>Permite o pagamento do boleto mesmo após o vencimento. Preencha com 0 para não permitir pagamento após a data de vencimento</small>
                    </div>
              </div>
              <hr style="border-top: 1px solid #fff;">
              <h5 class="mb-4">Multa</h5>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="code_multa">Código da multa*</label>
                    <select name="code_multa" id="code_multa" class="form-control" required>
                        <option value="">Selecione um código</option>
                        <option value="NAOTEMMULTA">Isento</option>
                        <option value="VALORFIXO">Valor Fixo</option>
                        <option value="PERCENTUAL">Percentual</option>
                    </select>
                  </div>
                <div class="form-group col-md-6">
                    <label for="data_multa">Data multa</label>
                    <input type="date" class="form-control" name="data_multa" id="data_multa" value="{{old('data_multa')}}" required>
                    <small>Obrigatório se informado código de multa VALOR FIXO ou PERCENTUAL. Deve ser maior que o vencimento e marca a data de início de cobrança
                        de multa (incluindo essa data)</small>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="taxa_multa">Taxa da multa* (Percentual)</label>
                    <input type="text" name="taxa_multa" value="{{old('taxa_multa')}}" class="form-control" placeholder="Insira apenas números separados por ponto. Ex: 1.7">
                    <small>Taxa Percentual de Multa do título. Obrigatória se informado código
                        de multa PERCENTUAL. Deve ser 0 para código de multa ISENTO</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="valor_multa">Valor da multa* (Valor Fixo)</label>
                    <input type="text" name="valor_multa" value="{{old('valor_multa')}}" class="form-control" placeholder="Insira apenas números separados por ponto. Ex: 1.7">
                    <small>Obrigatório se informado código de multa Valor Fixo. Deve ser 0 para código de multa ISENTO</small>
                </div>
              </div>
              <hr style="border-top: 1px solid #fff;">
              <h5 class="mb-4">Mora</h5>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="code_mora">Código da Mora*</label>
                    <select name="code_mora" id="code_mora" class="form-control" required>
                        <option value="">Selecione um código</option>
                        <option value="ISENTO">Isento</option>
                        <option value="VALORDIA">Valor diário</option>
                        <option value="TAXAMENSAL">Taxa Mensal</option>
                    </select>
                  </div>
                <div class="form-group col-md-6">
                    <label for="data_mora">Data mora</label>
                    <input type="date" class="form-control" name="data_mora" id="data_mora" value="{{old('data_mora')}}" required>
                    <small>Obrigatório se informado código de mora Valor diário, Taxa mensal. Deve ser maior que o vencimento e marca a data de início de cobrança</small>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="taxa_mora">Taxa de mora* (Taxa Mensal)</label>
                    <input type="text" name="taxa_mora" value="{{old('taxa_mora')}}" class="form-control" placeholder="Insira apenas números separados por ponto. Ex: 1.5" required>
                    <small>Percentual de Mora do título.
                        Obrigatória se informado código de mora Taxa Mensal</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="valor_mora">Valor da mora* (Valor Fixo)</label>
                    <input type="text" name="valor_mora" value="{{old('valor_mora')}}" class="form-control" placeholder="Insira apenas números separados por ponto. Ex: 1.0" required>
                    <small>Obrigatório se informado código de mora Taxa Mensal. Deve ser 0 para código de mora ISENTO.</small>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Gerar Boleto</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection