FROM nginx:alpine

# Remover configuração padrão
RUN rm /etc/nginx/conf.d/default.conf

# Copiar configuração customizada
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Expor portas
EXPOSE 80 443

# Comando padrão
CMD ["nginx", "-g", "daemon off;"]
