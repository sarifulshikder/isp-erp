import re

with open('/home/server/isp-erp/docker-compose.yml', 'r') as f:
    content = f.read()

old = '''  zammad-nginx:
    image: ghcr.io/zammad/zammad:latest
    container_name: zammad_nginx
    restart: always
    command: ["zammad-nginx"]
    ports:
      - "8090:8080"
    volumes:
      - zammad_data:/opt/zammad
    depends_on:
      - zammad-railsserver
      - zammad-websocket
    networks: [isp_net]'''

new = '''  zammad-nginx:
    image: ghcr.io/zammad/zammad:latest
    container_name: zammad_nginx
    restart: always
    command: ["zammad-nginx"]
    ports:
      - "8090:8080"
    environment:
      RAILS_ENV: production
      POSTGRESQL_HOST: zammad-postgresql
      POSTGRESQL_PORT: 5432
      POSTGRESQL_USER: zammad
      POSTGRESQL_PASS: zammad_pass
      POSTGRESQL_DB: zammad_production
      ELASTICSEARCH_HOST: zammad-elasticsearch
      ELASTICSEARCH_PORT: 9200
      REDIS_URL: redis://:${REDIS_PASS}@redis:6379
      ZAMMAD_SECRET_KEY_BASE: zammad_secret_key_base_change_this_32chars
    volumes:
      - zammad_data:/opt/zammad
    depends_on:
      - zammad-railsserver
      - zammad-websocket
    networks: [isp_net]'''

content = content.replace(old, new)

with open('/home/server/isp-erp/docker-compose.yml', 'w') as f:
    f.write(content)

print("Done!")
