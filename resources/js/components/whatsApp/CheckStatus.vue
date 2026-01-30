<template>
  <div>
    <li class="nav-item d-none d-sm-inline-block">
      <i v-if="isVerifying" class="text-warning fas fa-circle-notch" title="Verificando status">
        Verificando status do WhatsApp...
      </i>
      <i v-else-if="connected" class="text-success fas fa-circle" title="Online no WhatsApp">
        ONLINE WHATSAPP
      </i>
      <span v-else class="d-inline">
        <i class="text-danger fas fa-circle" title="Offline"> Offline WHATSAPP</i>
        <a :href="loginUrl" class="text-success ml-1"><strong>Conectar (escanear QR Code)</strong></a>
      </span>
    </li>
  </div>
</template>

<script>
export default {
  name: 'CheckStatusWhatsApp',
  data() {
    return {
      urlCheckStatus: '/dashboard/whatsapp/check-status',
      connected: false,
      loading: true,
      checking: false,
      interval: null
    }
  },
  computed: {
    isVerifying() {
      return this.loading || this.checking
    },
    loginUrl() {
      return '/dashboard/whatsapp'
    }
  },
  methods: {
    checkStatus() {
      if (this.checking) return;
      this.checking = true;

      axios
        .get(this.urlCheckStatus)
        .then(response => {
          // Novo formato: { success: true, message: "...", data: { status: "...", connected: true/false } }
          // Formato antigo: { status: "..." }
          const data = response.data.data || response.data;
          
          this.connected = data && (data.status === 'open' || data.connected === true);
          document.title = this.connected ? 'Sessão Online' : 'Sessão Offline';
        })
        .catch(() => {
          this.connected = false;
          document.title = 'Sessão Offline';
        })
        .finally(() => {
          this.loading = false;
          this.checking = false;
        });
    }
  },
  mounted() {
    this.checkStatus()
    this.interval = setInterval(this.checkStatus, 300000)
  },
  beforeDestroy() {
    if (this.interval) {
      clearInterval(this.interval)
    }
  }
}
</script>
