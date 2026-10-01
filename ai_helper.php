<?php
session_start();

// ==========================================
// 後端 API 區塊：專門接收前端傳來的文字，並轉交給 Ollama
// ==========================================
// 判斷是否為 AJAX 請求 (JavaScript fetch 發送過來的)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    
    // 告訴瀏覽器我們回傳的是 JSON 格式
    header('Content-Type: application/json');
    
    // 接收前端傳來的訊息
    $input = json_decode(file_get_contents('php://input'), true);
    $user_message = $input['message'] ?? '';

    if (empty($user_message)) {
        echo json_encode(['reply' => '請輸入您的問題喔！']);
        exit;
    }

    // 🔥 這是最核心的「系統提示詞 (System Prompt)」：限制 AI 的行為
    $system_prompt = "你是一位專業、溫暖且富有同理心的「失智症照護AI顧問」。\n" .
                     "你的唯一職責是解答有關「失智症 (Dementia)」、「長者照護」、「記憶退化」、「長照資源」相關的問題。\n" .
                     "【嚴格規則】：如果使用者詢問任何與上述無關的問題（例如：寫程式、翻譯、數學、政治、娛樂、天氣等），你絕對不能回答該問題的內容，必須禮貌地回覆：「您好，我是失智症照護小幫手，我只能回答與失智症及長照相關的問題喔！如果您有照護上的疑慮，歡迎隨時問我。」\n" .
                     "請務必使用「繁體中文」回答，並確保語氣穩定、專業且支持家屬。";

    // 準備傳給 Ollama 的資料格式 (JSON)
    $payload = json_encode([
        'model' => 'qwen2', // 如果你下載的是 llama3，請把這裡改成 llama3
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_message]
        ],
        'stream' => false // 為了簡單起見，我們一次性接收完整回覆，不使用串流
    ]);

    // 使用 PHP 的 cURL 發送請求給本機的 Ollama (預設 Port 是 11434)
    $ch = curl_init('http://localhost:11434/api/chat');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    // AI 思考需要時間，設定超時時間為 120 秒
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); 

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['reply' => '連線到 AI 伺服器失敗，請確認 Ollama 是否已經在背景執行。']);
    } else {
        $result = json_decode($response, true);
        $ai_reply = $result['message']['content'] ?? 'AI 目前無法回應，請稍後再試。';
        echo json_encode(['reply' => $ai_reply]);
    }
    exit; // 結束 API 執行，不要把下面的 HTML 印出來
}
// ==========================================
// 前端畫面區塊
// ==========================================
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>AI 照護顧問 - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        #chat-box {
            height: 500px;
            overflow-y: auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }
        .chat-message { margin-bottom: 15px; display: flex; flex-direction: column; }
        .chat-message.user { align-items: flex-end; }
        .chat-message.ai { align-items: flex-start; }
        .bubble {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: 20px;
            line-height: 1.6;
            word-wrap: break-word;
        }
        .user .bubble { background-color: #1976d2; color: white; border-bottom-right-radius: 5px; }
        .ai .bubble { background-color: #e9ecef; color: #212529; border-bottom-left-radius: 5px; }
        .typing-indicator { font-style: italic; color: #6c757d; font-size: 0.9em; display: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1976d2;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-heart-pulse-fill text-danger"></i> 失智症照護網</a>
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-sm btn-outline-light">← 返回首頁</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="fw-bold text-primary mb-1"><i class="bi bi-robot"></i> 24H AI 照護顧問</h4>
                    <p class="text-muted small">無論何時遇到照護難題，我都在這裡為您解答。您的對話僅保留在您的設備上，絕對隱私。</p>
                </div>
                
                <div class="card-body">
                    <div id="chat-box" class="mb-3">
                        <div class="chat-message ai">
                            <div class="bubble shadow-sm">
                                您好！我是您的專屬失智症照護 AI 顧問。請問您今天遇到什麼照護上的困難，或是想了解哪些失智症相關知識呢？
                            </div>
                        </div>
                    </div>
                    
                    <div id="typing" class="typing-indicator mb-2 px-2">AI 正在思考中，請稍候...</div>
                    
                    <div class="input-group">
                        <input type="text" id="user-input" class="form-control form-control-lg bg-light" placeholder="請輸入您的問題..." onkeypress="handleEnter(event)">
                        <button class="btn btn-primary px-4" onclick="sendMessage()"><i class="bi bi-send-fill"></i> 送出</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const userInput = document.getElementById('user-input');
    const typingIndicator = document.getElementById('typing');

    // 處理 Enter 鍵發送
    function handleEnter(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    }

    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        // 1. 將使用者的問題顯示在畫面上
        appendMessage('user', message);
        userInput.value = ''; // 清空輸入框
        
        // 顯示「AI 思考中」
        typingIndicator.style.display = 'block';
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            // 2. 透過 fetch (AJAX) 將訊息送給同一個檔案的 PHP 處理
            const response = await fetch('ai_helper.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // 告訴 PHP 這是 AJAX 請求
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            
            // 3. 將 AI 的回覆顯示在畫面上
            appendMessage('ai', data.reply);
        } catch (error) {
            appendMessage('ai', '系統發生錯誤，無法連接到 AI。請確認 Ollama 已啟動。');
        } finally {
            // 隱藏「AI 思考中」
            typingIndicator.style.display = 'none';
        }
    }

    function appendMessage(sender, text) {
        // 將文字裡的換行符號轉為 HTML 的 <br>
        const formattedText = text.replace(/\n/g, '<br>');
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        messageDiv.innerHTML = `<div class="bubble shadow-sm">${formattedText}</div>`;
        
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight; // 自動捲動到最下面
    }
</script>
</body>
</html>