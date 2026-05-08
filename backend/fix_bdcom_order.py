with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'r') as f:
    content = f.read()

old = """            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            // Web থেকে customer name নাও
            $webData  = $webInfo[$onuId] ?? null;
            $desc     = $webData['description'] ?? trim("{$vendor} {$fwVer}") ?: null;
            $onuId    = $this->buildOnuId($idx);
            $ponPort  = $this->extractPonPort($onuId);"""

new = """            $onuId    = $this->buildOnuId($idx);
            $ponPort  = $this->extractPonPort($onuId);
            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            // Web থেকে customer name নাও
            $webData  = $webInfo[$onuId] ?? null;
            $desc     = $webData['description'] ?? trim("{$vendor} {$fwVer}") ?: null;"""

content = content.replace(old, new)
with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'w') as f:
    f.write(content)
print("Done!")
