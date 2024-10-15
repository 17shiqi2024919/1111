<?php
session_start();

// 生成 CSRF 令牌
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 验证 CSRF 令牌
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 限制登录尝试
function check_login_attempts($username) {
    if (!isset($_SESSION['login_attempts'][$username])) {
        $_SESSION['login_attempts'][$username] = ['count' => 0, 'time' => time()];
    }
    
    if ($_SESSION['login_attempts'][$username]['count'] >= 5) {
        if (time() - $_SESSION['login_attempts'][$username]['time'] < 300) {
            return false; // 锁定 5 分钟
        } else {
            $_SESSION['login_attempts'][$username]['count'] = 0;
            $_SESSION['login_attempts'][$username]['time'] = time();
        }
    }
    
    return true;
}

// 记录登录尝试
function record_login_attempt($username, $success) {
    if ($success) {
        unset($_SESSION['login_attempts'][$username]);
    } else {
        $_SESSION['login_attempts'][$username]['count']++;
        $_SESSION['login_attempts'][$username]['time'] = time();
    }
}
