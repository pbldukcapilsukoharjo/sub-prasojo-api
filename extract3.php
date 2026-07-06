<?php
$content = file_get_contents('./docs/prasojo.sql');
$tables = ['layanan', 'jenis_ajuan', 'ilokasi_kecamatan'];
$result = [];

foreach ($tables as $table) {
    if (preg_match('/INSERT INTO `' . $table . '` \((.*?)\) VALUES\s*\((.*?)\)(?:,|;)/s', $content, $matches)) {
        $cols = array_map(function($c) { return trim(str_replace('`', '', $c)); }, explode(',', $matches[1]));
        $valsRaw = $matches[2];
        $vals = [];
        $cur = '';
        $inQuote = false;
        for ($i = 0; $i < strlen($valsRaw); $i++) {
            if ($valsRaw[$i] === "'") {
                if ($i > 0 && $valsRaw[$i-1] === '\\') {
                    $cur .= $valsRaw[$i];
                } else {
                    $inQuote = !$inQuote;
                }
            } else if ($valsRaw[$i] === ',' && !$inQuote) {
                $vals[] = trim($cur);
                $cur = '';
            } else {
                $cur .= $valsRaw[$i];
            }
        }
        $vals[] = trim($cur);
        
        $obj = [];
        foreach ($cols as $i => $c) {
            $v = isset($vals[$i]) ? $vals[$i] : null;
            if ($v !== null) $v = trim($v, "'");
            $obj[$c] = $v;
        }
        $result[$table] = $obj;
    }
}
echo json_encode($result, JSON_PRETTY_PRINT);
