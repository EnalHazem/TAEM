<?php

use AncientEgyptianMuseum\View\View;
use AncientEgyptianMuseum\Application;
use AncientEgyptianMuseum\Http\Request;
use AncientEgyptianMuseum\Support\Hash;
use AncientEgyptianMuseum\Http\Response;
use AncientEgyptianMuseum\Validation\Validator;
use Exception;

if (!function_exists('env')) {
    /**
     * الحصول على قيمة من متغيرات البيئة
     *
     * @param string $key المفتاح المطلوب
     * @param mixed $default القيمة الافتراضية إذا لم يوجد المفتاح
     * @return mixed
     */
    function env($key, $default = null)
    {
        // التحقق من وجود القيمة في متغيرات البيئة
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];

            // تحويل بعض القيم النصية إلى قيمها المنطقية أو الرقمية
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }

            return $value;
        }

        return $default;
    }
}

if (!function_exists('base_path')) {
    /**
     * الحصول على المسار الأساسي للتطبيق
     *
     * @param string $path المسار المراد إضافته إلى المسار الأساسي (اختياري)
     * @return string
     */
    function base_path($path = '')
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('class_basename')) {
    /**
     * الحصول على اسم الكلاس بدون مسار الفضاء
     *
     * @param string|object $class الكلاس أو الكائن
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('db')) {
    /**
     * الوصول إلى قاعدة البيانات
     *
     * @return DB
     */
    function db()
    {
        return app()->db;
    }
}

if (!function_exists('view_path')) {
    /**
     * الحصول على مسار ملفات العرض
     *
     * @param string $path المسار المراد إضافته (اختياري)
     * @return string
     */
    function view_path($path = '')
    {
        return base_path('views' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('config')) {
    /**
     * الوصول إلى إعدادات التطبيق
     *
     * @param string|array|null $key المفتاح أو مصفوفة المفاتيح والقيم
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $config = app()->config;

        if (is_null($key)) {
            return $config;
        }

        if (is_array($key)) {
            return $config->set($key);
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('config_path')) {
    /**
     * الحصول على مسار ملفات الإعدادات
     *
     * @param string $path المسار المراد إضافته (اختياري)
     * @return string
     */
    function config_path($path = '')
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('value')) {
    /**
     * إرجاع القيمة إذا كانت دالة يتم تنفيذها أولاً
     *
     * @param mixed $value القيمة
     * @param mixed ...$args المعطيات التي ستمرر إلى الدالة
     * @return mixed
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('public_path')) {
    /**
     * الحصول على مسار المجلد العام
     *
     * @param string $path المسار المراد إضافته (اختياري)
     * @return string
     */
    function public_path($path = '')
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('view')) {
    /**
     * إنشاء وعرض واجهة العرض
     *
     * @param string $view اسم واجهة العرض
     * @param array $params المعطيات المرسلة للعرض
     * @return string|void
     */
    function view($view, $params = [])
    {
        $viewContent = View::make($view, $params);

        if (headers_sent()) {
            echo $viewContent;
            return null;
        }

        return $viewContent;
    }
}

if (!function_exists('back')) {
    /**
     * إنشاء استجابة إعادة توجيه للصفحة السابقة
     *
     * @param int $status كود الحالة
     * @return Response
     */
    function back($status = 302)
    {
        return app()->response->back($status);
    }
}

if (!function_exists('app')) {
    /**
     * الحصول على كائن التطبيق أو خاصية من كائن التطبيق
     *
     * @param string|null $property اسم الخاصية المطلوبة
     * @return mixed|Application
     */
    function app($property = null)
    {
        static $instance = null;

        if (!$instance) {
            $instance = new Application();
        }

        if ($property !== null) {
            return $instance->$property ?? null;
        }

        return $instance;
    }
}

if (!function_exists('request')) {
    /**
     * الحصول على كائن الطلب أو قيمة محددة من الطلب
     *
     * @param string|array|null $key المفتاح أو مصفوفة من المفاتيح
     * @param mixed $default القيمة الافتراضية
     * @return mixed|Request
     */
    function request($key = null, $default = null)
    {
        $request = app()->request;

        if ($key === null) {
            return $request;
        }

        if (is_string($key)) {
            return $request->get($key, $default);
        }

        if (is_array($key)) {
            return $request->only($key);
        }

        return $request;
    }
}

if (!function_exists('validator')) {
    /**
     * إنشاء كائن التحقق من الصحة
     *
     * @param array $data البيانات المراد التحقق منها (اختياري)
     * @param array $rules قواعد التحقق (اختياري)
     * @return Validator
     */
    function validator(array $data = [], array $rules = [])
    {
        $validator = new Validator();

        if (!empty($data) && !empty($rules)) {
            return $validator->make($data, $rules);
        }

        return $validator;
    }
}

if (!function_exists('bcrypt')) {
    /**
     * تشفير البيانات باستخدام خوارزمية bcrypt
     *
     * @param string $data البيانات المراد تشفيرها
     * @param array $options خيارات التشفير
     * @return string
     */
    function bcrypt($data, array $options = [])
    {
        return Hash::make($data, $options);
    }
}

if (!function_exists('database_path')) {
    /**
     * الحصول على مسار قاعدة البيانات
     *
     * @param string $path المسار المراد إضافته (اختياري)
     * @return string
     */
    function database_path($path = '')
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('old')) {
    /**
     * الحصول على قيمة سابقة للنموذج
     *
     * @param string $key المفتاح
     * @param mixed $default القيمة الافتراضية إذا لم توجد القيمة السابقة
     * @return mixed
     */
    function old($key, $default = null)
    {
        $flashData = app()->session->hasFlash('old') ? app()->session->getFlash('old') : [];
        return $flashData[$key] ?? $default;
    }
}

if (!function_exists('asset')) {
    /**
     * إنشاء URL للملفات الثابتة في المجلد العام
     *
     * @param string $path المسار النسبي للملف
     * @return string
     */
    function asset($path)
    {
        $path = ltrim($path, '/');
        $basePath = rtrim(config('app.url', ''), '/');

        return "{$basePath}/{$path}";
    }
}

if (!function_exists('redirect')) {
    /**
     * إنشاء استجابة إعادة توجيه
     *
     * @param string $url المسار المراد التوجيه إليه
     * @param int $status كود الحالة
     * @return Response
     */
    function redirect($url, $status = 302)
    {
        return app()->response->redirect($url, $status);
    }
}

if (!function_exists('session')) {
    /**
     * الوصول إلى بيانات الجلسة
     *
     * @param string|array|null $key المفتاح أو مصفوفة المفاتيح والقيم
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        $session = app()->session;

        if (is_null($key)) {
            return $session;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $session->set($k, $v);
            }
            return true;
        }

        return $session->get($key, $default);
    }
}

if (!function_exists('storage_path')) {
    /**
     * الحصول على مسار مجلد التخزين
     *
     * @param string $path المسار المراد إضافته (اختياري)
     * @return string
     */
    function storage_path($path = '')
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('csrf_token')) {
    /**
     * الحصول على توكن CSRF
     *
     * @return string
     */
    function csrf_token()
    {
        $token = app()->session->get('_token');

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            app()->session->set('_token', $token);
        }

        return $token;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * إنشاء حقل إدخال مخفي لتوكن CSRF
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * إنشاء حقل إدخال مخفي لتحديد طريقة HTTP عند استخدام طرق غير GET و POST
     *
     * @param string $method طريقة HTTP
     * @return string
     */
    function method_field($method)
    {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}

if (!function_exists('url')) {
    /**
     * إنشاء URL كامل
     *
     * @param string $path المسار
     * @return string
     */
    function url($path = '')
    {
        $path = ltrim($path, '/');
        $basePath = rtrim(config('app.url', ''), '/');

        return $path ? "{$basePath}/{$path}" : $basePath;
    }
}

if (!function_exists('dd')) {
    /**
     * عرض المتغيرات وإيقاف التنفيذ
     *
     * @param mixed ...$vars المتغيرات المراد عرضها
     * @return void
     */
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }

        exit(1);
    }
}

if (!function_exists('abort')) {
    /**
     * رمي استثناء HTTP
     *
     * @param int $code كود الخطأ
     * @param string $message رسالة الخطأ
     * @return void
     */
    function abort($code, $message = '')
    {
        app()->response->setStatusCode($code);

        if ($message) {
            echo $message;
        }

        exit(1);
    }
}

if (!function_exists('now')) {
    /**
     * الحصول على كائن DateTime للوقت الحالي
     *
     * @return \DateTime
     */
    function now()
    {
        return new \DateTime();
    }
}

if (!function_exists('collect')) {
    /**
     * إنشاء مجموعة من البيانات
     *
     * @param mixed $items العناصر
     * @return array
     */
    function collect($items = [])
    {
        // يمكن استبدال هذا بكلاس Collection في حال وجوده في المشروع
        return (array) $items;
    }
}

if (!function_exists('route')) {
    /**
     * الوصول إلى مسار محدد
     *
     * @param string $name اسم المسار
     * @param array $parameters المعاملات للمسار
     * @return string
     */
    function route($name, $parameters = [])
    {
        return app()->route->getUrlFromName($name, $parameters);
    }
}

if (!function_exists('cache')) {
    /**
     * التعامل مع التخزين المؤقت
     * ملاحظة: هذه الدالة تفترض أن لديك خدمة تخزين مؤقت مضافة إلى التطبيق
     * إذا لم تكن موجودة، يمكنك إضافتها للتطبيق أو إنشاء كائن مخصص للتخزين المؤقت هنا
     *
     * @param string|array|null $key المفتاح أو مصفوفة من المفاتيح والقيم
     * @param mixed $default القيمة الافتراضية
     * @param int $ttl مدة الصلاحية بالثواني
     * @return mixed
     */
    function cache($key = null, $default = null, $ttl = 3600)
    {
        static $cacheInstance = null;

        if ($cacheInstance === null) {
            // محاولة الحصول على كائن التخزين المؤقت من التطبيق
            // تعديل هذا حسب كيفية تكامل خدمة التخزين المؤقت في تطبيقك
            $cacheProperty = 'cache';

            if (property_exists(app(), $cacheProperty)) {
                $cacheInstance = app()->$cacheProperty;
            } else {
                // إنشاء كائن بسيط للتخزين المؤقت إذا لم يكن موجودًا
                $cacheInstance = new class {
                    private $store = [];
                    private $expiration = [];

                    public function get($key, $default = null)
                    {
                        if (!isset($this->store[$key])) {
                            return $default;
                        }

                        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
                            $this->forget($key);
                            return $default;
                        }

                        return $this->store[$key];
                    }

                    public function set($key, $value, $ttl = null)
                    {
                        $this->store[$key] = $value;

                        if ($ttl !== null) {
                            $this->expiration[$key] = time() + $ttl;
                        }

                        return true;
                    }

                    public function has($key)
                    {
                        if (!isset($this->store[$key])) {
                            return false;
                        }

                        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
                            $this->forget($key);
                            return false;
                        }

                        return true;
                    }

                    public function forget($key)
                    {
                        unset($this->store[$key]);
                        unset($this->expiration[$key]);
                        return true;
                    }

                    public function flush()
                    {
                        $this->store = [];
                        $this->expiration = [];
                        return true;
                    }
                };
            }
        }

        if (is_null($key)) {
            return $cacheInstance;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $cacheInstance->set($k, $v, $ttl);
            }
            return true;
        }

        return $cacheInstance->get($key, $default);
    }
}
