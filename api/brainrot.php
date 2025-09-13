<?php
header('Content-Type: application/json');
date_default_timezone_set('UTC');

// --- Config ---
$discordtoken = getenv('DISCORD_TOKEN'); // gets your token from environment
if (!$discordtoken) {
    echo json_encode(['error' => 'DISCORD_TOKEN not set in environment']);
    exit;
}

// Your Discord channel ID
$channel = '1415622526154833982';
$url = "https://discord.com/api/v9/channels/{$channel}/messages?limit=1";

// --- Helper: GET JSON from Discord ---
function http_get_json($url, $authToken) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bot ' . $authToken,
            'Accept: application/json',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_TIMEOUT   => 10,
    ]);

    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code != 200) return [null, $code, $err ?: "HTTP $code"];
    $json = json_decode($body, true);
    return [$json, $code, null];
}

// --- Parse embed fields ---
function parse_message($message) {
    $fields = $message['embeds'][0]['fields'] ?? [];
    if (!$fields) return ['error' => 'No fields found'];

    $brainrot   = '';
    $generation = '';
    $players    = '';
    $link       = '';
    $script     = '';

    foreach ($fields as $field) {
        $name = strtolower($field['name'] ?? '');
        $val  = trim($field['value'] ?? '');

        if (strpos($name, 'brainrot') !== false) {
            $brainrot = trim(str_replace('```txt', '', str_replace('```', '', $val)));
        } elseif (strpos($name, 'generation') !== false) {
            $generation = trim(str_replace('```txt', '', str_replace('```', '', $val)));
        } elseif (strpos($name, 'players') !== false) {
            $players = trim(str_replace('```txt', '', str_replace('```', '', $val)));
        } elseif (strpos($name, 'link') !== false) {
            $link = $val; // keep markdown link format
        } elseif (strpos($name, 'script') !== false) {
            $script = trim(str_replace('```lua', '', str_replace('```', '', $val)));
        }
    }

    if (!$brainrot || !$generation || !$players || !$script) {
        return ['error' => 'Missing required fields in embed'];
    }

    return [
        'ok'         => true,
        'brainrot'   => $brainrot,
        'generation' => $generation,
        'players'    => $players,
        'link'       => $link,
        'script'     => $script,
    ];
}

// --- Main ---
[$json, $code, $err] = http_get_json($url, $discordtoken);
if ($err || !$json) {
    echo json_encode(['error' => $err ?: 'Request failed']);
    exit;
}

$message = $json[0] ?? null;
if (!$message) {
    echo json_encode(['error' => 'No message found']);
    exit;
}

$parsed = parse_message($message);
if (!isset($parsed['ok'])) {
    echo json_encode(['error' => $parsed['error'] ?? 'Parse error']);
    exit;
}

echo json_encode($parsed);
exit;
