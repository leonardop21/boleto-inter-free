@extends('Backend.Layouts.panel')
@section('title','Editar cliente -' . $customer->name)
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
            <h3 class="card-title">Editar {{$customer->name}}</h3>
          </div>
          <form method="post" action="{{route('backend.customer.update', ['id' => $customer->id])}}">
           @method('put')
           @csrf
            <div class="card-body">
              <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Nome*</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{old('name', $customer->name)}}" placeholder="Nome completo" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email">E-mail*</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{old('email', $customer->email)}}" placeholder="E-mail" required>
                    </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="corporate_name">Razão Social</label>
                    <input type="text" class="form-control" name="corporate_name" id="corporate_name" value="{{old('corporate_name', $customer->corporate_name)}}" placeholder="Razão Social">
                </div>
                <div class="form-group col-md-6">
                    <label for="fantasy_name">Nome Fantasia</label>
                    <input type="text" class="form-control" name="fantasy_name" id="fantasy_name" value="{{old('fantasy_name', $customer->fantasy_name)}}" placeholder="Nome fantasia">
                </div>
              </div>
              <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="cpf_cnpj">CPF/CNPJ*</label>
                        <input type="text" class="form-control" name="cpf_cnpj" id="cpf_cnpj" value="{{old('cpf_cnpj', $customer->cpf_cnpj)}}" placeholder="Rua amazonas" minlength="11" maxlength="14" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="address">Endereço*</label>
                        <input type="text" class="form-control" name="address" id="address" value="{{old('address', $customer->address)}}" placeholder="Rua amazonas" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="address_number">Número*</label>
                        <input type="text" class="form-control" name="address_number" id="address_number" value="{{old('address_number', $customer->address_number)}}" placeholder="2758 B" required>
                    </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="state">Estado*</label>
                    <select name="state" id="state" class="form-control" required>
                        @foreach($states as $state)
                            <option value="{{$state}}" {{($state == $customer->state)? 'selected':'' }}>{{$state}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="city">Cidade*</label>
                    <input type="text" class="form-control" name="city" id="city" value="{{old('city', $customer->city)}}" placeholder="Cidade" required>
                </div>
                <div class="form-group col-md-2">
                    <label for="zipcode">Cep*</label>
                    <input type="text" class="form-control" name="zipcode" id="zipcode" value="{{old('zipcode', $customer->zipcode)}}" placeholder="Cep" required>
                </div>
              </div>
              <div class="form-row">
                  <div class="-form-group col-md-6">
                    <label for="district">Bairro*</label>
                    <input type="text" class="form-control" name="district" id="district" value="{{old('district', $customer->district)}}" placeholder="Bairro" required>
                  </div>
                  <div class="-form-group col-md-6">
                    <label for="complement">Complemento</label>
                    <input type="text" class="form-control" name="complement" id="complement" value="{{old('complement', $customer->complement)}}" placeholder="Casa">
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="phone">Telefone</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="{{old('phone', $customer->phone)}}" placeholder="45 3242-0000">
                </div>
                <div class="form-group col-md-4">
                    <label for="whatsapp">WhatsApp</label>
                    <input type="text" class="form-control" name="whatsapp" id="whatsapp" value="{{old('whatsapp', $customer->whatsapp)}}" placeholder="45 9 9999-9999">
                </div>
                <div class="form-group col-md-4">
                    <label for="telegram">Telegram</label>
                    <input type="text" class="form-control" name="telegram" id="telegram" value="{{old('telegram', $customer->telegram)}}" placeholder="nickname">
                </div>
              </div>
              <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="email_notify">Notificação por e-mail*</label>
                    <select name="email_notify" id="email_notify" class="form-control" required>
                        <option value="1" {{$customer->email_notify == 1 ? 'selected' : ''}}>SIM</option>
                        <option value="0" {{$customer->email_notify == 0 ? 'selected' : ''}}>NÃO</option>
                      </select>
                      <small>Envia o boleto por e-mail ao cliente</small>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="whatsapp_notify">Notificação por WhatsApp*</label>
                    <select name="whatsapp_notify" id="whatsapp_notify" class="form-control" required>
                        <option value="">Selecione...</option>
                        <option value="1" {{$customer->whatsapp_notify == 1 ? 'selected' : ''}}>SIM</option>
                        <option value="0" {{$customer->whatsapp_notify == 0 ? 'selected' : ''}}>NÃO</option>
                      </select>
                      <small>Envia o boleto por WhatsApp ao cliente (Disponível com assinatura adicional)</small>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="telegram_notify">Notificação por Telegram</label>
                    <select name="telegram_notify" id="telegram_notify" class="form-control">
                        <option value="0" {{$customer->telegram_notify == 0 ? 'selected' : ''}}>NÃO</option>
                        <option value="1" {{$customer->telegram_notify == 0 ? 'selected' : ''}}>SIM</option>
                      </select>
                      <small>Envia o boleto via telegram ao cliente (Ainda não implementado)</small>
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group col">
                        <label>Serviços*</label>
                        <div class="select2-blue">
                            <select name="services[]" class="select2" multiple="multiple" data-placeholder="Selecione um serviço" data-dropdown-css-class="select2-blue" style="width: 100%;" required>
                                @foreach($services as $service)
                                 <option value="{{$service->id}}" {{in_array($service->id, $selectedService) ? 'selected' : ''}}>{{$service->name}}</option>
                                @endforeach
                            </select>
                        </div>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="generate_nfe">Gerar Nota fiscal</label>
                    <select name="generate_nfe" id="generate_nfe" class="form-control" required>
                        <option value="1" {{$customer->generate_nfe == 1 ? "selected" : ''}}>SIM</option>
                        <option value="0" {{$customer->generate_nfe == 0 ? "selected" : ''}}>NÃO</option>
                    </select>
                    <small>Se marcado como sim, gera NF automaticamente</small>
                  </div>
                <div class="form-group col-md-6">
                    <label for="customer_nfe_type_id">Selecione o serviço para geração de NFe</label>
                    <select name="customer_nfe_type_id" id="status" class="form-control">
                        <option value="">Selecione um serviço para gerar NFe</option>
                        @foreach($nfeTypes as $nfe)
                            <option value="{{$nfe->id}}" {{$customer->customer_nfe_type_id == $nfe->id ? 'selected' : ''}}>{{$nfe->code_item_service}} - {{$nfe->name}} </option>
                        @endforeach
                      </select>
                      <small>Status Inativo não gera boleto</small>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col">
                    <label for="expiration_day_boleto">Melhor dia para pagamento*</label>
                    <select name="expiration_day_boleto" id="expiration_day_boleto" class="form-control" required>
                        <option value="">Selecione um dia</option>
                        @for($i=1; $i <= 30; $i++)
                            <option value="{{str_pad($i, 2, '0', STR_PAD_LEFT)}}" {{$customer->expiration_day_boleto == $i ? 'selected' : ''}}>{{$i}}</option>
                        @endfor
                    </select>
                    <small>Ex: Todo dia 10 será o vencimento do boleto</small>
                  </div>
                <div class="form-group col">
                    <label for="date_to_pay_after_due_date">Quantos dias para pagar após vencimento?*</label>
                    <select name="date_to_pay_after_due_date" id="date_to_pay_after_due_date" class="form-control" required>
                        <option value="">Selecione um dia</option>
                        @for($i=1; $i <= 30; $i++)
                            <option value="{{str_pad($i, 2, '0', STR_PAD_LEFT)}}" {{$customer->date_to_pay_after_due_date == $i ? 'selected' : ''}}>{{$i}}</option>
                        @endfor
                    </select>
                    <small>Quantos dias após o vencimento, o boleto ainda poderá ser pago?*</small>
                  </div>
                <div class="form-group col">
                    <label for="status">Status do cliente no sistema</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" {{$customer->status == 1 ? 'selected' : ''}}>ATIVO</option>
                        <option value="0" {{$customer->status == 0 ? 'selected' : ''}}>INATIVO</option>
                      </select>
                      <small>Status Inativo não gera boleto</small>
                  </div>
              </div>
              <hr style="border-top: 1px solid #fff;">
              <h5 class="mb-4">Multa</h5>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="code_multa">Código da multa*</label>
                    <select name="code_multa" id="code_multa" class="form-control" required>
                        <option value="">Selecione um código</option>
                        <option value="NAOTEMMULTA" {{$customer->code_multa == "NAOTEMMULTA" ? 'selected' : ''}}>Isento</option>
                        <option value="VALORFIXO" {{$customer->code_multa == "VALORFIXO" ? 'selected' : ''}}>Valor Fixo</option>
                        <option value="PERCENTUAL" {{$customer->code_multa == "PERCENTUAL" ? 'selected' : ''}}>Percentual</option>
                    </select>
                  </div>
                <div class="form-group col-md-6">
                    <label for="data_multa">Multa a partir do dia</label>
                    <select name="data_multa" id="data_multa" class="form-control">
                        <option value="">Selecione um dia</option>
                        @for($i=1; $i <= 30; $i++)
                            <option value="{{str_pad($i, 2, '0', STR_PAD_LEFT)}}" {{$customer->data_multa == $i ? 'selected' : ''}}>{{$i}}</option>
                        @endfor
                      </select>
                      <small>Obrigatório se informado código de multa VALOR FIXO ou PERCENTUAL. Deve ser maior que o vencimento e marca a data de início de cobrança
                        de multa (incluindo essa data)</small>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="taxa_multa">Taxa da multa* (Percentual)</label>
                    <input type="text" name="taxa_multa" class="form-control" value="{{$customer->taxa_multa}}" placeholder="Insira apenas números separados por ponto. Ex: 1.7">
                    <small>Taxa Percentual de Multa do título. Obrigatória se informado código
                        de multa PERCENTUAL. Deve ser 0 para código de multa ISENTO</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="valor_multa">Valor da multa* (Valor Fixo)</label>
                    <input type="text" name="valor_multa" class="form-control" value="{{$customer->valor_multa}}" placeholder="Insira apenas números separados por ponto. Ex: 1.7">
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
                        <option value="ISENTO" {{$customer->code_mora == "ISENTO" ? 'selected' : ''}}>Isento</option>
                        <option value="VALORDIA" {{$customer->code_mora == "VALORDIA" ? 'selected' : ''}}>Valor diário</option>
                        <option value="TAXAMENSAL" {{$customer->code_mora == "TAXAMENSAL" ? 'selected' : ''}}>Taxa Mensal</option>
                    </select>
                  </div>
                <div class="form-group col-md-6">
                    <label for="data_mora">Mora a partir do dia</label>
                    <select name="data_mora" id="data_mora" class="form-control" required>
                        <option value="">Selecione um dia</option>
                        @for($i=1; $i <= 30; $i++)
                            <option value="{{str_pad($i, 2, '0', STR_PAD_LEFT)}}" {{$customer->data_mora == $i ? 'selected' : ''}}>{{$i}}</option>
                        @endfor
                      </select>
                      <small>Obrigatório se informado código de mora Valor diário, Taxa mensal. Deve ser maior que o vencimento e marca a data de início de cobrança</small>
                  </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="taxa_mora">Taxa de mora* (Taxa Mensal)</label>
                    <input type="text" name="taxa_mora" class="form-control" value="{{$customer->taxa_mora}}" placeholder="Insira apenas números separados por ponto. Ex: 1.5">
                    <small>Percentual de Mora do título.
                        Obrigatória se informado código de mora Taxa Mensal</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="valor_mora">Valor da mora* (Valor Fixo)</label>
                    <input type="text" name="valor_mora" class="form-control" value="{{$customer->valor_mora}}" placeholder="Insira apenas números separados por ponto. Ex: 1.0">
                    <small>Obrigatório se informado código de mora Taxa Mensal. Deve ser 0 para código de mora ISENTO.</small>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Atualizar cliente</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .select2-container--default .select2-selection--multiple {
        background-color: #343a40 !important;
        border: 1px solid #6c757d;
    }
    .dark-mode .select2-blue .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #343a40;
        border: none;
    }
    .dark-mode .select2-container .select2-search--inline .select2-search__field {
        display: none;
    }
</style>
@endsection