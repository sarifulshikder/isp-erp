with open('/home/server/isp-erp/backend/app/Filament/Resources/OltDeviceResource.php', 'r') as f:
    content = f.read()

old = """                TextInput::make('username')->required(),
                TextInput::make('password')->password()->required(),"""

new = """                TextInput::make('username')->required(),
                TextInput::make('password')->password()->required(),
                TextInput::make('snmp_port')->numeric()->default(161)->label('SNMP Port'),
                TextInput::make('snmp_community')->default('public')->label('SNMP Community'),"""

content = content.replace(old, new)
with open('/home/server/isp-erp/backend/app/Filament/Resources/OltDeviceResource.php', 'w') as f:
    f.write(content)
print("Done!")
