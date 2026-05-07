with open('/home/server/isp-erp/docker-compose.yml', 'r') as f:
    content = f.read()

old = """      - N8N_SECURE_COOKIE=false"""

new = """      - N8N_SECURE_COOKIE=false
      - N8N_HOST=10.5.31.34
      - N8N_PORT=5678
      - N8N_PROTOCOL=http
      - WEBHOOK_URL=http://10.5.31.34:5678/"""

content = content.replace(old, new)

with open('/home/server/isp-erp/docker-compose.yml', 'w') as f:
    f.write(content)

print("Done!")
