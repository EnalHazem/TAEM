<?php

namespace AncientEgyptianMuseum\Support;

class Config implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * جميع عناصر التكوين المخزنة
     *
     * @var array
     */
    protected array $items = [];

    /**
     * تخزين مؤقت للقيم المحملة مسبقًا
     *
     * @var array
     */
    protected array $cache = [];

    /**
     * إنشاء مثيل جديد للتكوين
     *
     * @param array $items العناصر الأولية للتكوين
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * التحقق مما إذا كان المفتاح أو المفاتيح موجودة في التكوين
     *
     * @param string|array $keys المفتاح أو المفاتيح للتحقق منها
     * @return bool
     */
    public function has($keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $subConfig = $this->items;

            if (Arr::exists($this->items, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (!is_array($subConfig) || !Arr::exists($subConfig, $segment)) {
                    return false;
                }

                $subConfig = $subConfig[$segment];
            }
        }

        return true;
    }

    /**
     * الحصول على قيمة من التكوين
     *
     * @param string|array|null $key المفتاح للحصول على القيمة
     * @param mixed $default القيمة الافتراضية إذا كان المفتاح غير موجود
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        if (is_null($key)) {
            return $this->items;
        }

        // تحقق من وجود القيمة في التخزين المؤقت
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $value = Arr::get($this->items, $key, $default);
        
        // تخزين القيمة في التخزين المؤقت للاستخدام اللاحق
        $this->cache[$key] = $value;
        
        return $value;
    }

    /**
     * الحصول على عدة قيم من التكوين في وقت واحد
     *
     * @param array $keys المفاتيح للحصول على القيم
     * @return array
     */
    public function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = $this->get($key, $default);
        }

        return $config;
    }

    /**
     * تعيين قيمة في التكوين
     *
     * @param string|array $key المفتاح أو مصفوفة من المفاتيح والقيم
     * @param mixed $value القيمة للتعيين
     * @return void
     */
    public function set($key, $value = null): void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $k => $v) {
            // إزالة القيمة من التخزين المؤقت عند تعديلها
            if (isset($this->cache[$k])) {
                unset($this->cache[$k]);
            }

            Arr::set($this->items, $k, $v);
        }
    }

    /**
     * دفع قيمة إلى مصفوفة موجودة في التكوين
     *
     * @param string $key المفتاح للمصفوفة
     * @param mixed $value القيمة للإضافة إلى المصفوفة
     * @return void
     */
    public function push(string $key, $value): void
    {
        $array = $this->get($key, []);

        if (!is_array($array)) {
            throw new \InvalidArgumentException("القيمة في '{$key}' ليست مصفوفة.");
        }

        $array[] = $value;

        // إزالة القيمة من التخزين المؤقت
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }

        $this->set($key, $array);
    }

    /**
     * أضف قيمًا متعددة إلى مصفوفة موجودة في التكوين
     *
     * @param string $key المفتاح للمصفوفة
     * @param array $values القيم للإضافة
     * @return void
     */
    public function pushMany(string $key, array $values): void
    {
        foreach ($values as $value) {
            $this->push($key, $value);
        }
    }

    /**
     * الحصول على جميع عناصر التكوين
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * التحقق مما إذا كان المفتاح موجودًا في التكوين
     *
     * @param string $key المفتاح للتحقق منه
     * @return bool
     */
    public function exists(string $key): bool
    {
        return Arr::exists($this->items, $key);
    }

    /**
     * حذف عنصر من التكوين
     *
     * @param string|array $keys المفتاح أو المفاتيح للحذف
     * @return void
     */
    public function forget($keys): void
    {
        Arr::forget($this->items, $keys);

        // حذف القيم من التخزين المؤقت
        $keys = (array) $keys;
        foreach ($keys as $key) {
            if (isset($this->cache[$key])) {
                unset($this->cache[$key]);
            }
        }
    }

    /**
     * دمج مصفوفة مع التكوين الحالي
     *
     * @param array $items العناصر للدمج
     * @return void
     */
    public function merge(array $items): void
    {
        $this->items = array_merge($this->items, $items);
        
        // إعادة تعيين التخزين المؤقت
        $this->cache = [];
    }

    /**
     * دمج عميق لمصفوفة مع التكوين الحالي
     *
     * @param array $items العناصر للدمج بعمق
     * @return void
     */
    public function mergeRecursive(array $items): void
    {
        $this->items = array_merge_recursive($this->items, $items);
        
        // إعادة تعيين التخزين المؤقت
        $this->cache = [];
    }

    /**
     * تحميل التكوين من ملف
     *
     * @param string $path مسار الملف
     * @param string $key مفتاح التكوين (اختياري)
     * @return bool نجاح التحميل
     */
    public function loadFromFile(string $path, ?string $key = null): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $config = require $path;

        if (!is_array($config)) {
            return false;
        }

        if ($key) {
            $this->set($key, $config);
        } else {
            $this->merge($config);
        }

        return true;
    }

    /**
     * تخزين التكوين في ملف
     *
     * @param string $path مسار الملف
     * @param string|null $key مفتاح التكوين للتخزين (اختياري)
     * @return bool نجاح التخزين
     */
    public function saveToFile(string $path, ?string $key = null): bool
    {
        $data = $key ? $this->get($key) : $this->all();
        
        if (!is_array($data)) {
            return false;
        }
        
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        
        return file_put_contents($path, $content) !== false;
    }

    /**
     * إعادة تعيين التخزين المؤقت
     *
     * @return void
     */
    public function flushCache(): void
    {
        $this->cache = [];
    }

    /**
     * الحصول على عدد عناصر التكوين
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * ArrayAccess: التحقق مما إذا كان المفتاح موجودًا
     *
     * @param mixed $offset المفتاح للتحقق منه
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess: الحصول على قيمة
     *
     * @param mixed $offset المفتاح للحصول على القيمة
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess: تعيين قيمة
     *
     * @param mixed $offset المفتاح للتعيين
     * @param mixed $value القيمة للتعيين
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess: حذف قيمة
     *
     * @param mixed $offset المفتاح للحذف
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->forget($offset);
    }

    /**
     * IteratorAggregate: الحصول على مكرر للعناصر
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }
}