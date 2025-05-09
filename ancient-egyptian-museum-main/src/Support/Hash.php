<?php

namespace AncientEgyptianMuseum\Support;

class Hash
{
    /**
     * الخوارزميات المدعومة للتشفير
     */
    public const ALGO_BCRYPT = PASSWORD_BCRYPT;
    public const ALGO_ARGON2I = PASSWORD_ARGON2I;
    public const ALGO_ARGON2ID = PASSWORD_ARGON2ID;
    public const ALGO_DEFAULT = PASSWORD_DEFAULT;

    /**
     * إنشاء هاش لكلمة المرور
     *
     * @param string $value القيمة المراد تشفيرها
     * @param int|string $algo خوارزمية التشفير (افتراضياً PASSWORD_DEFAULT)
     * @param array $options خيارات إضافية للتشفير
     * @return string القيمة المشفرة
     */
    public static function make($value, $algo = self::ALGO_DEFAULT, array $options = [])
    {
        return password_hash($value, $algo, $options);
    }

    /**
     * إنشاء هاش بواسطة Bcrypt
     * 
     * @param string $value القيمة المراد تشفيرها
     * @param int $cost مستوى التكلفة (10-12 مناسب لمعظم التطبيقات)
     * @return string القيمة المشفرة
     */
    public static function bcrypt($value, $cost = 12)
    {
        return self::make($value, self::ALGO_BCRYPT, ['cost' => $cost]);
    }

    /**
     * إنشاء هاش بواسطة Argon2i (أكثر أمانًا من Bcrypt)
     * 
     * @param string $value القيمة المراد تشفيرها
     * @param array $options خيارات إضافية للتشفير
     * @return string القيمة المشفرة
     */
    public static function argon2i($value, array $options = [])
    {
        return self::make($value, self::ALGO_ARGON2I, $options);
    }

    /**
     * إنشاء هاش بواسطة Argon2id (الأكثر أمانًا)
     * 
     * @param string $value القيمة المراد تشفيرها
     * @param array $options خيارات إضافية للتشفير
     * @return string القيمة المشفرة
     */
    public static function argon2id($value, array $options = [])
    {
        return self::make($value, self::ALGO_ARGON2ID, $options);
    }

    /**
     * إنشاء هاش بخوارزمية محددة (SHA1, SHA256, MD5, ...)
     * ملاحظة: لا ينصح باستخدام هذه الدوال لكلمات المرور، بل للتحقق فقط
     *
     * @param string $value القيمة المراد تشفيرها
     * @param string $algo الخوارزمية (sha1, sha256, sha512, ...)
     * @param bool $raw_output إرجاع النتيجة كبيانات ثنائية (true) أو كسلسلة hex (false)
     * @return string القيمة المشفرة
     */
    public static function hash($value, $algo = 'sha256', $raw_output = false)
    {
        return hash($algo, $value, $raw_output);
    }

    /**
     * التحقق من صحة كلمة المرور مقابل قيمة مشفرة
     *
     * @param string $value القيمة الأصلية غير المشفرة
     * @param string $hashedValue القيمة المشفرة للمقارنة
     * @return bool نتيجة المقارنة
     */
    public static function verify($value, $hashedValue)
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * التحقق ما إذا كان يجب إعادة تشفير كلمة المرور (مثلاً عند تغيير خيارات التشفير)
     *
     * @param string $hashedValue القيمة المشفرة
     * @param int|string $algo خوارزمية التشفير
     * @param array $options خيارات إضافية للتشفير
     * @return bool إذا كان يجب إعادة التشفير
     */
    public static function needsRehash($hashedValue, $algo = self::ALGO_DEFAULT, array $options = [])
    {
        return password_needs_rehash($hashedValue, $algo, $options);
    }

    /**
     * إنشاء هاش سريع للتأكد من سلامة البيانات باستخدام HMAC
     *
     * @param string $value القيمة المراد تشفيرها
     * @param string $key المفتاح السري
     * @param string $algo الخوارزمية المستخدمة (sha256 افتراضياً)
     * @return string القيمة المشفرة
     */
    public static function hmac($value, $key, $algo = 'sha256')
    {
        return hash_hmac($algo, $value, $key);
    }

    /**
     * إنشاء سلسلة عشوائية آمنة بالطول المحدد
     *
     * @param int $length طول السلسلة المطلوبة
     * @return string سلسلة عشوائية آمنة
     */
    public static function random($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * إنشاء رمز تحقق (token) للاستخدام في روابط إعادة تعيين كلمة المرور أو التحقق
     *
     * @param int $length طول الرمز
     * @return string رمز التحقق
     */
    public static function token($length = 64)
    {
        return self::random($length);
    }

    /**
     * مقارنة سلاسل بطريقة آمنة ضد هجمات التوقيت
     *
     * @param string $knownString السلسلة المعروفة
     * @param string $userString السلسلة المدخلة من المستخدم
     * @return bool نتيجة المقارنة
     */
    public static function secureCompare($knownString, $userString)
    {
        return hash_equals($knownString, $userString);
    }

    /**
     * حساب قيمة هاش ملف
     * 
     * @param string $filePath مسار الملف
     * @param string $algo الخوارزمية المستخدمة (sha256 افتراضياً)
     * @return string|false قيمة الهاش أو false في حالة الفشل
     */
    public static function file($filePath, $algo = 'sha256')
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        return hash_file($algo, $filePath);
    }
}