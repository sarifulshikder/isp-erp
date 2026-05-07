with open('/home/server/isp-erp/docker-compose.yml', 'r') as f:
    content = f.read()

old = """      - N8N_BASIC_AUTH_PASSWORD=${N8N_BASIC_AUTH_PASSWORD}"""

new = """      - N8N_BASIC_AUTH_PASSWORD=${N8N_BASIC_AUTH_PASSWORD}
      - N8N_SECURE_COOKIE=false"""

content = content.replace(old, new)

with open('/home/server/isp-erp/docker-compose.yml', 'w') as f:
    f.write(content)

print("Done!")
