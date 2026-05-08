with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'r') as f:
    content = f.read()

old = """    public function pollAndSave(OltDevice $olt): int
    {
        $statuses     = $this->getOnuStatuses();
        $rxPowers     = $this->getOnuRxPowers();
        $macs         = $this->getOnuMacs();
        $descriptions = $this->getOnuDescriptions();
        $vendors      = $this->getOnuVendors();"""

new = """    // Web থেকে ONU info আনো (customer name, MAC, ONU ID)
    public function getOnuInfoFromWeb(OltDevice $olt): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "http://{$olt->ip}:{$olt->port}/onuintfstate.asp",
            CURLOPT_USERPWD        => "{$olt->username}:{$olt->password}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        $info = [];
        // Parse: intfName[0]="EPON0/1:1"; ... description[0]="Anis";
        preg_match_all('/intfName\[(\d+)\]="([^"]+)"/', $html, $names, PREG_SET_ORDER);
        preg_match_all('/description\[(\d+)\]="([^"]*)"/', $html, $descs, PREG_SET_ORDER);
        preg_match_all('/MACAddress\[(\d+)\]="([^"]+)"/', $html, $macs, PREG_SET_ORDER);

        foreach ($names as $m) {
            $i = $m[1];
            $info[$m[2]] = [
                'description' => $descs[$i][2] ?? null,
                'mac_web'     => isset($macs[$i][2]) ? strtoupper(str_replace('.', ':', $macs[$i][2])) : null,
            ];
        }
        return $info;
    }

    public function pollAndSave(OltDevice $olt): int
    {
        $statuses     = $this->getOnuStatuses();
        $rxPowers     = $this->getOnuRxPowers();
        $macs         = $this->getOnuMacs();
        $descriptions = $this->getOnuDescriptions();
        $vendors      = $this->getOnuVendors();
        $webInfo      = $this->getOnuInfoFromWeb($olt);"""

content = content.replace(old, new)

# Use web description
old2 = """            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            $desc     = trim("{$vendor} {$fwVer}") ?: null;"""

new2 = """            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            // Web থেকে customer name নাও
            $webData  = $webInfo[$onuId] ?? null;
            $desc     = $webData['description'] ?? trim("{$vendor} {$fwVer}") ?: null;"""

content = content.replace(old2, new2)

with open('/home/server/isp-erp/backend/app/Services/BdcomService.php', 'w') as f:
    f.write(content)
print("Done!")
