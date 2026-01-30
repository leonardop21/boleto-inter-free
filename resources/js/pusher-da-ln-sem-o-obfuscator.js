var elements = document.getElementsByClassName("copy_pix");
      Array.from(elements).forEach(function(element) {
          element.addEventListener("click", function() {
            var getPx = document.getElementById("px-cp_ps");
            var textToCopy = getPx.textContent;
            var textArea = document.createElement("textarea");
                textArea.value = textToCopy;
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
              try {
                  var successful = document.execCommand("copy");
                  var msg = successful ? "Código copiado com sucesso! Agora, abra o seu aplicativo bancário e navegue até a opção 'Pix Copia e cola' para colar o código copiado e finalizar a transação. ATENÇÃO. Confira os dados antes de efetuar o pagamento" : "Não foi possível copiar";
                  alert(msg);
              } catch (err) {
                  console.log("Erro ao copiar: ", err);
              }
              document.body.removeChild(textArea);
          });
      });


      const token = window.location.pathname.split('/').pop();

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = false;

    // Key e cluster devem vir do backend (ex.: Laravel config/broadcasting) – não usar valores fixos
    var pusherKey = window.PUSHER_APP_KEY || '';
    var pusherCluster = window.PUSHER_APP_CLUSTER || '';
    if (!pusherKey || !pusherCluster) return;
    var pusher = new Pusher(pusherKey, { cluster: pusherCluster });

    var channel = pusher.subscribe('payment-channel-' + token);
    channel.bind('payment-received-' + token, function(data) {
      const redirectUrl = data['redirect_url'];
      if (redirectUrl) {
          window.location.href = redirectUrl;
      }
});