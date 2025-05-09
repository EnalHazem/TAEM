<?php

namespace AncientEgyptianMuseum\Support;

class Session
{
    /**
     * مفتاح الرسائل الوميضية (Flash Messages) في الجلسة
     */
    protected const FLASH_KEY = 'flash_messages';
    
    /**
     * مفتاح رسائل التنبيه في الجلسة
     */
    protected const ALERT_KEY = 'alert_messages';
    
    /**
     * مفتاح بيانات المستخدم المسجل الدخول
     */
    protected const AUTH_USER_KEY = 'authenticated_user';
    
    /**
     * مؤشر على إذا كانت الجلسة قد بدأت
     *
     * @var bool
     */
    protected bool $isStarted = false;
    
    /**
     * مفتاح CSRF الحالي
     *
     * @var string|null
     */
    protected ?string $csrfToken = null;
    
    /**
     * مدة صلاحية مفتاح CSRF بالثواني (30 دقيقة افتراضيًا)
     *
     * @var int
     */
    protected int $csrfTokenExpiry = 1800;
    
    /**
     * إعداد وتهيئة الجلسة
     *
     * @param array $options خيارات إضافية للجلسة
     */
    public function __construct(array $options = [])
    {
        $this->start($options);
        
        // معالجة الرسائل الوميضية عند إنشاء الجلسة
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            $flashMessage['remove'] = true;
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
        
        // إذا لم يكن هناك مفتاح CSRF، قم بإنشاء واحد جديد
        if (!$this->has('csrf_token') || !$this->has('csrf_token_expiry') || 
            time() > $this->get('csrf_token_expiry')) {
            $this->regenerateCsrfToken();
        } else {
            $this->csrfToken = $this->get('csrf_token');
        }
    }
    
    /**
     * بدء جلسة جديدة إذا لم تكن قد بدأت بالفعل
     *
     * @param array $options خيارات إضافية للجلسة
     * @return bool نجاح العملية
     */
    public function start(array $options = []): bool
    {
        if ($this->isStarted) {
            return true;
        }
        
        // تعيين خيارات الجلسة الآمنة افتراضيًا
        if (empty($options)) {
            $cookieParams = session_get_cookie_params();
            $options = [
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
                'cookie_lifetime' => $cookieParams['lifetime'],
                'gc_maxlifetime' => 7200 // عمر الجلسة: ساعتان
            ];
        }
        
        // تعيين خيارات الجلسة
        foreach ($options as $key => $value) {
            ini_set("session.$key", $value);
        }
        
        // بدء الجلسة
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->isStarted = true;
        return true;
    }
    
    /**
     * تعيين رسالة وميضية (تظهر مرة واحدة فقط)
     *
     * @param string $key المفتاح
     * @param mixed $message الرسالة
     * @return self
     */
    public function setFlash(string $key, $message): self
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
        
        return $this;
    }
    
    /**
     * الحصول على رسالة وميضية
     *
     * @param string $key المفتاح
     * @param mixed $default القيمة الافتراضية إذا لم يتم العثور على المفتاح
     * @return mixed
     */
    public function getFlash(string $key, $default = false)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? $default;
    }
    
    /**
     * التحقق من وجود رسالة وميضية
     *
     * @param string $key المفتاح
     * @return bool
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }
    
    /**
     * إضافة رسالة نجاح وميضية
     *
     * @param string $message الرسالة
     * @return self
     */
    public function success(string $message): self
    {
        return $this->setFlash('success', $message);
    }
    
    /**
     * إضافة رسالة خطأ وميضية
     *
     * @param string $message الرسالة
     * @return self
     */
    public function error(string $message): self
    {
        return $this->setFlash('error', $message);
    }
    
    /**
     * إضافة رسالة تحذير وميضية
     *
     * @param string $message الرسالة
     * @return self
     */
    public function warning(string $message): self
    {
        return $this->setFlash('warning', $message);
    }
    
    /**
     * إضافة رسالة معلومات وميضية
     *
     * @param string $message الرسالة
     * @return self
     */
    public function info(string $message): self
    {
        return $this->setFlash('info', $message);
    }
    
    /**
     * تعيين قيمة في الجلسة
     *
     * @param string $key المفتاح
     * @param mixed $value القيمة
     * @return self
     */
    public function set(string $key, $value): self
    {
        $_SESSION[$key] = $value;
        
        return $this;
    }
    
    /**
     * الحصول على قيمة من الجلسة
     *
     * @param string $key المفتاح
     * @param mixed $default القيمة الافتراضية إذا لم يتم العثور على المفتاح
     * @return mixed
     */
    public function get(string $key, $default = false)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * التحقق من وجود مفتاح في الجلسة
     *
     * @param string $key المفتاح
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * التحقق من وجود مفتاح في الجلسة (نفس وظيفة has)
     *
     * @param string $key المفتاح
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }
    
    /**
     * إزالة مفتاح من الجلسة
     *
     * @param string $key المفتاح
     * @return self
     */
    public function remove(string $key): self
    {
        unset($_SESSION[$key]);
        
        return $this;
    }
    
    /**
     * الحصول على قيمة من الجلسة ثم إزالتها
     *
     * @param string $key المفتاح
     * @param mixed $default القيمة الافتراضية إذا لم يتم العثور على المفتاح
     * @return mixed
     */
    public function pull(string $key, $default = false)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        
        return $value;
    }
    
    /**
     * إزالة جميع البيانات من الجلسة
     *
     * @return self
     */
    public function clear(): self
    {
        $_SESSION = [];
        
        return $this;
    }
    
    /**
     * تدمير الجلسة بالكامل
     *
     * @return bool
     */
    public function destroy(): bool
    {
        $this->clear();
        
        // إزالة ملف الكوكي الخاص بالجلسة
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // تدمير الجلسة
        return session_destroy();
    }
    
    /**
     * إعادة توليد معرف الجلسة
     *
     * @param bool $deleteOldSession إذا كان يجب حذف الجلسة القديمة
     * @return bool
     */
    public function regenerateId(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }
    
    /**
     * تعيين معلومات المستخدم المصادق عليه في الجلسة
     *
     * @param array $user بيانات المستخدم
     * @param bool $rememberMe إذا كان يجب تذكر المستخدم
     * @return self
     */
    public function setAuthUser(array $user, bool $rememberMe = false): self
    {
        $this->set(self::AUTH_USER_KEY, $user);
        
        // إذا كان المستخدم يريد تذكر تسجيل الدخول، قم بتمديد عمر الجلسة
        if ($rememberMe) {
            // تعيين عمر الجلسة لمدة 30 يومًا
            ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60);
            ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
        }
        
        // إعادة توليد معرف الجلسة لمنع هجمات اختطاف الجلسة
        $this->regenerateId();
        
        return $this;
    }
    
    /**
     * الحصول على معلومات المستخدم المصادق عليه
     *
     * @return array|null
     */
    public function getAuthUser(): ?array
    {
        return $this->get(self::AUTH_USER_KEY, null);
    }
    
    /**
     * التحقق من تسجيل دخول المستخدم
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->has(self::AUTH_USER_KEY);
    }
    
    /**
     * تسجيل خروج المستخدم
     *
     * @return self
     */
    public function logout(): self
    {
        $this->remove(self::AUTH_USER_KEY);
        $this->regenerateId();
        
        return $this;
    }
    
    /**
     * إعادة توليد رمز CSRF
     *
     * @return string
     */
    public function regenerateCsrfToken(): string
    {
        $this->csrfToken = bin2hex(random_bytes(32));
        $this->set('csrf_token', $this->csrfToken);
        $this->set('csrf_token_expiry', time() + $this->csrfTokenExpiry);
        
        return $this->csrfToken;
    }
    
    /**
     * الحصول على رمز CSRF الحالي
     *
     * @return string
     */
    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
    
    /**
     * التحقق من صحة رمز CSRF
     *
     * @param string|null $token الرمز المراد التحقق منه
     * @return bool
     */
    public function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($this->csrfToken)) {
            return false;
        }
        
        // التحقق من تطابق الرمز
        return hash_equals($this->csrfToken, $token);
    }
    
    /**
     * إنشاء حقل إدخال CSRF لاستخدامه في النماذج
     *
     * @return string
     */
    public function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
    }
    
    /**
     * الحصول على جميع بيانات الجلسة
     *
     * @return array
     */
    public function all(): array
    {
        return $_SESSION;
    }
    
    /**
     * حفظ بيانات الجلسة
     *
     * @return bool
     */
    public function save(): bool
    {
        return session_write_close();
    }
    
    /**
     * تعيين وقت انتهاء صلاحية رمز CSRF
     *
     * @param int $seconds عدد الثواني
     * @return self
     */
    public function setCsrfTokenExpiry(int $seconds): self
    {
        $this->csrfTokenExpiry = $seconds;
        
        return $this;
    }
    
    /**
     * دمج مصفوفة مع القيم الموجودة في الجلسة
     *
     * @param array $data البيانات المراد دمجها
     * @return self
     */
    public function merge(array $data): self
    {
        $_SESSION = array_merge($_SESSION, $data);
        
        return $this;
    }
    
    /**
     * تنظيف الجلسة عند تدمير الكائن
     */
    public function __destruct()
    {
        $this->removeFlashMessages();
    }
    
    /**
     * إزالة الرسائل الوميضية التي تم عرضها
     */
    private function removeFlashMessages(): void
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => $flashMessage) {
            if ($flashMessage['remove']) {
                unset($flashMessages[$key]);
            }
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}