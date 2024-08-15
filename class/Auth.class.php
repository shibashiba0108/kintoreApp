<?php

namespace kintore\class;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google_Client;
use Google_Service_Oauth2;
use ReCaptcha\ReCaptcha;

class Auth
{
    private $db;
    private $session;
    private $recaptchaSecret;
    private $googleClient;

    public function __construct($db, $session)
    {
        $this->db = $db;
        $this->session = $session;
        $this->recaptchaSecret = getenv('RECAPTCHA_SECRET');
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $this->googleClient->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $this->googleClient->setRedirectUri('http://localhost:8888/DT/oauth2callback');
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }

    public function getRecentRegistrations()
    {
        $query = "SELECT COUNT(*) AS recent_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['recent_users'] ?? 0;
    }

    public function verifyRecaptcha($recaptchaResponse)
    {
        $recaptcha = new ReCaptcha($this->recaptchaSecret);
        $resp = $recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()) {
            return ['success' => false, 'message' => 'reCAPTCHA verification failed.'];
        }
        return ['success' => true];
    }

    public function register($userName, $email, $password)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '無効なメールアドレスです。'];
        } elseif (!preg_match('/^[a-zA-Z0-9]{1,15}$/', $userName)) {
            return ['success' => false, 'message' => 'ユーザーIDは1〜15文字の半角英数字で入力してください。'];
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password)) {
            return ['success' => false, 'message' => 'パスワードは8文字以上で、大文字・小文字・数字をそれぞれ1文字以上含む必要があります。'];
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE user_name = ? OR email = ?");
            $stmt->execute([$userName, $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                return ['success' => false, 'message' => 'このユーザーIDまたはメールアドレスは既に使用されています。'];
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $verificationCode = bin2hex(random_bytes(16));
                $stmt = $this->db->prepare("INSERT INTO users (user_name, email, password, verification_code) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userName, $email, $passwordHash, $verificationCode]);
                $userId = $this->db->getLastId(); // 'user_name' ではなく 'user_id' を取得
                $this->session->updateSessionUserId($userId); // 'updateSessionUserId' を使用
                if ($this->sendVerificationEmail($email, $verificationCode)) {
                    return ['success' => true, 'user_id' => $userId];
                } else {
                    return ['success' => false, 'message' => 'メールの送信に失敗しました。'];
                }
            }
        }
    }

    public function login($loginIdentifier, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_name = ? OR email = ?");
        $stmt->execute([$loginIdentifier, $loginIdentifier]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['is_verified']) {
                    $this->session->updateSessionUserId($user['id']); // 'user_name' ではなく 'user_id' を使用
                    return ['success' => true, 'user_id' => $user['id']];
                } else {
                    return ['success' => false, 'message' => 'メール認証が完了していません。'];
                }
            } else {
                return ['success' => false, 'message' => 'ユーザー名またはパスワードが正しくありません。'];
            }
        } else {
            return ['success' => false, 'message' => 'ユーザー名またはパスワードが正しくありません。'];
        }
    }

    public function googleLogin($email, $password)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '無効なメールアドレスです。'];
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password)) {
            return ['success' => false, 'message' => 'パスワードは8文字以上で、大文字・小文字・数字をそれぞれ1文字以上含む必要があります。'];
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                return ['success' => false, 'message' => 'このメールアドレスは既に使用されています。'];
            } else {
                $userName = bin2hex(random_bytes(8)); // ユーザーIDを自動生成
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $verificationCode = bin2hex(random_bytes(16));
                $stmt = $this->db->prepare("INSERT INTO users (user_name, email, password, verification_code) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userName, $email, $passwordHash, $verificationCode]);
                $user_name = $this->db->getLastId(); // 登録されたユーザーのIDを取得
                $this->session->updateSessionUserId($user_name); // セッションテーブルのuser_idを更新
                if ($this->sendVerificationEmail($email, $verificationCode)) {
                    return ['success' => true, 'user_name' => $user_name];
                } else {
                    return ['success' => false, 'message' => 'メールの送信に失敗しました。'];
                }
            }
        }
    }

    public function resetPassword($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '無効なメールアドレスです。'];
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                $resetCode = bin2hex(random_bytes(16));
                $stmt = $this->db->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
                $stmt->execute([$resetCode, $email]);
                if ($this->sendResetEmail($email, $resetCode)) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'メールの送信に失敗しました。'];
                }
            } else {
                return ['success' => false, 'message' => 'このメールアドレスは登録されていません。'];
            }
        }
    }

    public function handleGoogleLogin($code)
    {
        $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
        $this->googleClient->setAccessToken($token);
        $oauth = new Google_Service_Oauth2($this->googleClient);
        $profile = $oauth->userinfo->get();

        $email = $profile->email;
        $userName = bin2hex(random_bytes(8)); // ユーザーIDを自動生成

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($user) {
            $this->session->updateSessionUserId($user['id']); // セッションテーブルのuser_idを更新
            return ['success' => true, 'user_name' => $user['id']];
        } else {
            $passwordHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            $verificationCode = bin2hex(random_bytes(16));
            $stmt = $this->db->prepare("INSERT INTO users (user_id, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$userName, $email, $passwordHash, $verificationCode]);
            $user_name = $this->db->getLastId(); // 登録されたユーザーのIDを取得
            $this->session->updateSessionUserId($user_name); // セッションテーブルのuser_idを更新
            if ($this->sendVerificationEmail($email, $verificationCode)) {
                return ['success' => true, 'user_name' => $user_name];
            } else {
                return ['success' => false, 'message' => 'メールの送信に失敗しました。'];
            }
        }
    }

    public function verifyResetCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE verification_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updatePassword($code, $newPassword)
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE verification_code = ?");
        $stmt->execute([$passwordHash, $code]);
    }

    private function sendVerificationEmail($email, $verificationCode)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.lolipop.jp';
            $mail->SMTPAuth = true;
            $mail->Username = 'shiba@dt30.net';
            $mail->Password = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSLを使用
            $mail->Port = 465; // SSLのポート番号

            $mail->setFrom('shiba@dt30.net', 'Kintrail Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "Click the link to verify your email: <a href='http://localhost:8888/DT/kintore/contents/verify.php?code=$verificationCode'>Verify Email</a>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    private function sendResetEmail($email, $resetCode)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.lolipop.jp';
            $mail->SMTPAuth = true;
            $mail->Username = 'shiba@dt30.net';
            $mail->Password = 'Ie63v_QnkFm-nXy5-7hsw';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSLを使用
            $mail->Port = 465; // SSLのポート番号

            $mail->setFrom('shiba@dt30.net', 'Kintrail Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body = "Click the link to reset your password: <a href='http://localhost:8888/DT/kintore/contents/reset_password.php?code=$resetCode'>Reset Password</a>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function verifyEmail($verificationCode)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE verification_code = ?");
        $stmt->execute([$verificationCode]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            $stmt = $this->db->prepare("UPDATE users SET is_verified = 1 WHERE verification_code = ?");
            $stmt->execute([$verificationCode]);
            $this->session->updateSessionUserId($user['id']); // セッション情報を更新
            return ['success' => true, 'user_name' => $user['id']];
        } else {
            return ['success' => false, 'message' => '無効な検証コードです。'];
        }
    }
}
