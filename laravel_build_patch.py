with open('/home/server/isp-erp/docker-compose.yml', 'r') as f:
    content = f.read()

old = """  laravel:
    image: webdevops/php-nginx:8.4-alpine
    container_name: isp_laravel"""

new = """  laravel:
    build:
      context: .
      dockerfile: Dockerfile.laravel
    container_name: isp_laravel"""

content = content.replace(old, new)
with open('/home/server/isp-erp/docker-compose.yml', 'w') as f:
    f.write(content)
print("Done!")
