with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'r') as f:
    content = f.read()

old = """        foreach ($raw as $idx => $value) {
            $val = (int) $value;
            if ($val > 0) {
                // Convert: value / 10 = dBm (e.g. 234 → -23.4 dBm needs sign check)
                // BDCOM stores as positive integer, actual is negative dBm
                $powers[$idx] = round(-($val / 10), 2);
            }
        }"""

new = """        foreach ($raw as $idx => $value) {
            $val = (int) $value;
            if ($val > 0) {
                // BDCOM: unit is 0.01 dBm, stored as positive integer
                // 3100 → -31.00 dBm, 1247 → -12.47 dBm
                $powers[$idx] = round(-($val / 100), 2);
            }
        }"""

content = content.replace(old, new)
with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'w') as f:
    f.write(content)
print("Done!")
