with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'r') as f:
    content = f.read()

old = """    // ONU description: OID .4
    public function getOnuDescriptions(): array
    {
        return $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.4');
    }

    // ONU ID (interface name): OID .2
    public function getOnuIds(): array
    {
        return $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.2');
    }

    // PON port extract from ONU ID (EPON0/1:1 → PON1)
    private function extractPonPort(string $onuId): string
    {
        if (preg_match('/EPON0\\/(\\d+):/', $onuId, $m)) {
            return 'PON' . $m[1];
        }
        return 'Unknown';
    }"""

new = """    // ONU description: OID .4 (hex firmware version)
    public function getOnuDescriptions(): array
    {
        $raw = $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.4');
        $descs = [];
        foreach ($raw as $idx => $hex) {
            // Hex-STRING: 56 31 2E 30 00 00 → "V1.0"
            $bytes = explode(' ', trim($hex));
            $str = '';
            foreach ($bytes as $b) {
                $c = hexdec($b);
                if ($c > 31 && $c < 127) $str .= chr($c);
            }
            $descs[$idx] = trim($str) ?: null;
        }
        return $descs;
    }

    // ONU vendor: OID .1
    public function getOnuVendors(): array
    {
        return $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.1');
    }

    // Build ONU ID from index: 66→EPON0/1:1, 67→EPON0/1:2 etc.
    private function buildOnuId(string $idx): string
    {
        $i = (int)$idx - 66;
        $pon = (int)($i / 16) + 1;
        $onu = ($i % 16) + 1;
        return "EPON0/{$pon}:{$onu}";
    }

    // PON port from index
    private function extractPonPort(string $onuId): string
    {
        if (preg_match('/EPON0\\/(\\d+):/', $onuId, $m)) {
            return 'PON' . $m[1];
        }
        return 'Unknown';
    }"""

content = content.replace(old, new)

# Also fix pollAndSave to use buildOnuId
old2 = """            $onuId    = $onuIds[$idx] ?? "EPON-{$idx}";"""
new2 = """            $onuId    = $this->buildOnuId($idx);"""
content = content.replace(old2, new2)

# Remove getOnuIds from pollAndSave
old3 = """        $statuses     = $this->getOnuStatuses();
        $rxPowers     = $this->getOnuRxPowers();
        $macs         = $this->getOnuMacs();
        $descriptions = $this->getOnuDescriptions();
        $onuIds       = $this->getOnuIds();"""
new3 = """        $statuses     = $this->getOnuStatuses();
        $rxPowers     = $this->getOnuRxPowers();
        $macs         = $this->getOnuMacs();
        $descriptions = $this->getOnuDescriptions();
        $vendors      = $this->getOnuVendors();"""
content = content.replace(old3, new3)

# Use vendor as description fallback
old4 = """            $desc     = $descriptions[$idx] ?? null;"""
new4 = """            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            $desc     = trim("{$vendor} {$fwVer}") ?: null;"""
content = content.replace(old4, new4)

with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'w') as f:
    f.write(content)
print("Done!")
