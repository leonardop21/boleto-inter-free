@extends('Backend.Layouts.panel')
@section('title','Painel')
@section('content')

<section id="infos">
    <div class="container">
        <div class="row">
            @if(isset($saldoErrorMessage) && $saldoErrorMessage)
                <div class="col-12">
                    <div class="alert alert-danger">
                        {{ $saldoErrorMessage }}
                    </div>
                </div>
            @endif
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                   <div class="inner">
                      <h3 id="saldo-conta">R$ {{convertBRL($saldo)}}</h3>
                      <p>Saldo disponível</p>
                   </div>
                   <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                   </div>
                   <a href="#" onclick="renderSaldo()" class="small-box-footer">Consultar Saldo <i class="fas fa-sync-alt"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-info">
                  <div class="inner">
                     <h3>{{$qtdBoleto}}</h3>
                     <p>Boletos</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-money-check"></i>
                  </div>
                  <a href="{{route('backend.fatura.boleto.index')}}" class="small-box-footer">Saiba Mais <i class="fas fa-arrow-circle-right"></i></a>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-danger">
                  <div class="inner">
                     <h3>{{$qtdService}}</h3>
                     <p>Serviços</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-terminal"></i>
                  </div>
                  <a href="{{route('backend.service.index')}}" class="small-box-footer">Saiba Mais <i class="fas fa-arrow-circle-right"></i></a>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-warning">
                  <div class="inner">
                     <h3>{{$qtdCustomer}}</h3>
                     <p>Clientes</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-users"></i>
                  </div>
                  <a href="{{route('backend.customer.index')}}" class="small-box-footer">Saiba Mais <i class="fas fa-arrow-circle-right"></i></a>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-success">
                  <div class="inner">
                     <h3>R$ {{convertBRL($valueBoletoPaidMonth)}}</h3>
                     <p>Boletos quitados no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-bill-wave"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-info">
                  <div class="inner">
                     <h3>R$ {{convertBRL($valueBoletoOpenMonth)}}</h3>
                     <p>Boletos em aberto no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-check"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-danger">
                  <div class="inner">
                     <h3>R$ {{convertBRL($valueBoletoNotPaidMonth)}}</h3>
                     <p>Boletos vencidos no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-check"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-success">
                  <div class="inner">
                     <h3>R$ {{convertBRL($predictionPriceAllService)}}</h3>
                     <p>Projeção todos os serviços (Ativos)</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-check"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-success">
                  <div class="inner">
                     <h3>R$ {{convertBRL($valuePixPaidMonth)}}</h3>
                     <p>Pix recebidos no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-bill-wave"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-info">
                  <div class="inner">
                     <h3>R$ {{convertBRL($valuePixOpenMonth)}}</h3>
                     <p>Pix em aberto no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-check"></i>
                  </div>
               </div>
            </div>
            <div class="col-lg-3 col-6">
               <div class="small-box bg-info">
                  <div class="inner">
                     <h3>R$ {{convertBRL($totalBoletoAndPix)}}</h3>
                     <p>Pix e boleto recebido no mês</p>
                  </div>
                  <div class="icon">
                    <i class="fa fa-money-check"></i>
                  </div>
               </div>
            </div>
         </div>
    </div>
</section>

@endsection

@section('js')
<script>
    const url = "{{route('backend.get-saldo')}}";
    
    async function getSaldo() {
        try {
            let res = await fetch(url);
            let response = await res.json();
            
            // Novo formato: { success: true, message: "...", data: { disponivel: ..., erro_saldo_inter: ... } }
            // Formato antigo: { disponivel: ..., erro_saldo_inter: ... }
            if (response.success && response.data) {
                return response.data;
            } else if (response.success === false) {
                // Em caso de erro no novo formato
                return {
                    disponivel: 0,
                    erro_saldo_inter: response.message || 'Não foi possível consultar o saldo do Banco Inter.'
                };
            }
            
            // Fallback para formato antigo (compatibilidade)
            return response;
        } catch (error) {
            console.log(error);
            return {
                disponivel: 0,
                erro_saldo_inter: 'Não foi possível consultar o saldo do Banco Inter.'
            };
        }
    }

    async function renderSaldo() {
        let saldoConta = document.getElementById("saldo-conta");
        saldoConta.innerText = "Atualizando..";
        let saldoDisponivel = await getSaldo();
        if (saldoDisponivel.erro_saldo_inter) {
            alert(saldoDisponivel.erro_saldo_inter);
        }
        let valor = saldoDisponivel.disponivel || 0;
        let saldo = valor.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
        saldoConta.innerText = saldo;
    }
</script>
@endsection
