<?php
// .envからAPIキーを読み込む簡易的な処理
$env = parse_ini_file('.env');
$api_key = $env['OPENAI_API_KEY'] ?? '';

$response_text = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    $query = $_POST['query'];

    // OpenAI API エンドポイント
    $url = 'https://api.openai.com/v1/chat/completions';

    // リクエストデータの設定
    $data = [
        'model' => 'gpt-5-mini', // 指定のモデル名
        'messages' => [
            [
                'role' => 'system',
                'content' => 'ユーザーの入力から最も重要なキーワードを1つ選び、次の形式で出力してください。説明は不要です。 原語: [語句] / 英訳: [English Word]'
            ],
            [
                'role' => 'user',
                'content' => $query
            ]
        ],
        'temperature' => 0.3,
    ];

    // cURLによるリクエスト（ライブラリ不使用）
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200) {
        $json = json_decode($result, true);
        $response_text = $json['choices'][0]['message']['content'] ?? 'エラーが発生しました。';
    } else {
        $response_text = "APIエラーが発生しました。Status: " . $status;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Simple Keyword Translator</title>
</head>
<body>
    <h1>キーワード抽出・英訳</h1>
    <form method="POST">
        <input type="text" name="query" placeholder="問いかけを入力してください" style="width: 300px;" required>
        <button type="submit">送信</button>
    </form>

    <?php if ($response_text): ?>
        <div style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;">
            <strong>結果:</strong><br>
            <pre><?php echo htmlspecialchars($response_text); ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>