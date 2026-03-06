<?php
// scripts/check_smtp.php
$hosts = [
    'mail.knowledgesource.in',
    'cpanel27.interactivedns.com'
];
$ports = [25, 26, 465, 587];
$timeout = 5; // seconds

function try_connect($host, $port, $timeout) {
    $result = ['host'=>$host,'port'=>$port,'connected'=>false,'ssl'=>false,'banner'=>null,'ehlo'=>null,'starttls'=>false,'error'=>null];
    $errno = 0; $errstr = '';
    $addr = $host . ':' . $port;
    // For port 465 try implicit ssl first
    if ($port == 465) {
        $transport = 'ssl://';
        $fp = @stream_socket_client($transport.$host.':'.$port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if ($fp) {
            stream_set_timeout($fp, $timeout);
            $result['connected'] = true;
            $result['ssl'] = true;
            $banner = fgets($fp);
            $result['banner'] = trim($banner);
            fclose($fp);
            return $result;
        } else {
            $result['error'] = "$errstr ($errno)";
            return $result;
        }
    }
    // For other ports try plain tcp
    $fp = @stream_socket_client('tcp://'.$host.':'.$port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        $result['error'] = "$errstr ($errno)";
        return $result;
    }
    stream_set_timeout($fp, $timeout);
    $result['connected'] = true;
    // read banner
    $banner = fgets($fp);
    $result['banner'] = trim($banner);
    // send EHLO
    $hostname = gethostname() ?: 'localhost';
    fwrite($fp, "EHLO $hostname\r\n");
    $resp = '';
    $starttls = false;
    // read multi-line response (lines starting with 250- ...)
    $start = microtime(true);
    while (($line = fgets($fp)) !== false) {
        $resp .= $line;
        if (stripos($line, 'STARTTLS') !== false) $starttls = true;
        // end of 250 response typically starts with '250 ' (space)
        if (preg_match('/^250\s/i', $line)) break;
        if ((microtime(true)-$start) > $timeout) break;
    }
    $result['ehlo'] = trim($resp);
    $result['starttls'] = $starttls;
    fclose($fp);
    return $result;
}

$results = [];
foreach ($hosts as $h) {
    foreach ($ports as $p) {
        echo "Testing $h:$p ...\n";
        $r = try_connect($h,$p,$timeout);
        $results[] = $r;
        echo "  connected: " . ($r['connected'] ? 'yes' : 'no') . "\n";
        if ($r['banner']) echo "  banner: {$r['banner']}\n";
        if ($r['ehlo']) echo "  ehlo: " . substr(str_replace("\r\n"," ", $r['ehlo']),0,400) . "\n";
        echo "  starttls: " . ($r['starttls'] ? 'yes' : 'no') . "\n";
        if ($r['error']) echo "  error: {$r['error']}\n";
        echo "\n";
    }
}

// Summary
echo "Summary:\n";
foreach ($results as $r) {
    printf("%s:%d connected=%s starttls=%s ssl=%s error=%s\n", $r['host'],$r['port'],$r['connected']?'yes':'no',$r['starttls']?'yes':'no',$r['ssl']?'yes':'no',$r['error']??'');
}

?>