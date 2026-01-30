<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <!-- Instruções -->
        <div class="col-12 col-lg-6 mt-4 mb-4">
          <div v-if="status === 'qrcode'">
            <ol class="font1-3em linheH-2-3">
              <li>Abra o WhatsApp Business</li>
              <li>Clique em <i class="fas fa-ellipsis-v" aria-hidden="true"></i> do lado superior direito e depois em dispositivos conectados</li>
              <li>Clique em "Conectar dispositivo" e aponte o seu celular para o QRCODE ao lado</li>
            </ol>
          </div>
          <div v-else-if="status === 'closed'" class="m-3">
            <h5 class="font1-3em linheH-2-3">
              A sessão foi encerrada automaticamente, aguarde, a página será recarregada em 3 segundos.<br />
              <small>Caso a página não seja recarregada <a href="/dashboard/whatsapp">clique aqui</a></small>
            </h5>
          </div>
          <div v-else-if="status === 'open'" class="m-3">
            <h5 class="font1-3em lineH-1-8">
              Identificamos que você já está conectado ao WhatsApp, aguarde enquanto lhe redirecionamos para o painel!
            </h5>
          </div>
        </div>

        <!-- QRCode -->
        <div class="col-12 col-lg-6 mt-4 mb-4">
          <div v-if="loading" class="text-center">
            <h1>Carregando...</h1>
          </div>
          <div v-else-if="status === 'qrcode'" class="text-center">
            <div v-if="qrcodeImage" class="qrcode-container">
              <img 
                :src="qrcodeImage" 
                alt="QR Code WhatsApp" 
                class="qrcode-image"
              />
            </div>
            <div v-else class="text-muted">
              <p>Aguardando QRCode...</p>
            </div>
          </div>
          <div v-else class="text-center">
            <h1>{{ displayStatus }}</h1>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginWhatsApp',
  props: {
    qrcodeOrStatus: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      qrcode: '',
      status: '',
      loading: true,
      interval: null
    }
  },
  computed: {
    qrcodeImage() {
      if (!this.qrcode) return null;
      const trimmed = this.qrcode.trim();
      if (trimmed && trimmed.startsWith('data:image')) {
        return trimmed;
      }
      return null;
    },
    displayStatus() {
      if (this.status === 'open') {
        return 'Conectado';
      }
      return this.status;
    }
  },
  methods: {
    parseInitialData() {
      try {
        const data = JSON.parse(this.qrcodeOrStatus);
        this.status = (data.status || 'qrcode').toLowerCase();
        this.qrcode = data.qrcode || '';
      } catch (error) {
        this.status = 'error';
        this.qrcode = '';
      }
    },
    updateStatus() {
      axios
        .get('/dashboard/whatsapp/qrcode-login-update')
        .then(response => {
          // Novo formato: { success: true, message: "...", data: { qrcode: "...", status: "..." } }
          // Formato antigo: { qrcode: "...", status: "..." }
          const data = response.data.data || response.data;
          
          this.status = (data.status || 'qrcode').toLowerCase();
          this.qrcode = data.qrcode || '';
          
          this.handleStatusChange();
        })
        .catch(error => {
          this.loading = false;
          // Em caso de erro, tenta extrair mensagem do novo formato
          if (error.response && error.response.data && error.response.data.message) {
            console.error('Erro ao atualizar status:', error.response.data.message);
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
    handleStatusChange() {
      if (this.status === 'open') {
        localStorage.removeItem('sharaeClosedCount');
        this.clearInterval();
        this.redirectPage('/dashboard', 3000);
      } else if (this.status === 'closed') {
        this.handleClosedStatus();
      }
    },
    handleClosedStatus() {
      const increments = parseInt(localStorage.getItem('sharaeClosedCount')) || 0;
      
      if (increments <= 2) {
        localStorage.setItem('sharaeClosedCount', increments + 1);
        this.reloadPage(3000);
      } else {
        this.clearInterval();
      }
    },
    redirectPage(page, seconds) {
      setTimeout(() => {
        window.location.href = page;
      }, seconds);
    },
    reloadPage(seconds) {
      setTimeout(() => {
        location.reload();
      }, seconds);
    },
    clearInterval() {
      if (this.interval) {
        clearInterval(this.interval);
        this.interval = null;
      }
    }
  },
  mounted() {
    this.parseInitialData();
    
    if (this.status === 'open') {
      localStorage.removeItem('sharaeClosedCount');
      this.loading = false;
      this.redirectPage('/dashboard', 3000);
    } else {
      this.loading = false;
      this.interval = setInterval(() => {
        this.updateStatus();
      }, 10000);
    }
  },
  beforeDestroy() {
    this.clearInterval();
  }
}
</script>

<style scoped>
.font1-3em {
  font-size: 1.3em;
}

.lineH-1-8 {
  line-height: 1.8em;
}

.linheH-2-3 {
  line-height: 2.3em;
}

.qrcode-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  border-radius: 8px;
}

.qrcode-image {
  max-width: 100%;
  height: auto;
  border: 2px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
