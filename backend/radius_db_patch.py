with open('/home/server/isp-erp/backend/config/database.php', 'r') as f:
    content = f.read()

radius_conn = """
        'radius' => [
            'driver' => 'mysql',
            'host' => 'mysql',
            'port' => '3306',
            'database' => 'radius',
            'username' => 'isp_user',
            'password' => 'ISP@Secure#2026',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],"""

old = "'connections' => ["
new = "'connections' => [" + radius_conn

content = content.replace(old, new)

with open('/home/server/isp-erp/backend/config/database.php', 'w') as f:
    f.write(content)

print("Done!")
