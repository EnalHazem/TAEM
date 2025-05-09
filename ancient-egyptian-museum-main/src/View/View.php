<?php

namespace AncientEgyptianMuseum\View;

/**
 * View class responsible for rendering views with high performance and flexibility
 */
class View
{
    /**
     * Cache for storing compiled views
     *
     * @var array
     */
    protected static array $viewCache = [];
    
    /**
     * Template data shared across all views
     *
     * @var array
     */
    protected static array $sharedData = [];
    
    /**
     * Cache expiration time in seconds (0 = no cache)
     *
     * @var int
     */
    protected static int $cacheExpiration = 0;
    
    /**
     * Current theme name
     *
     * @var string|null
     */
    protected static ?string $theme = null;
    
    /**
     * Render a view with provided parameters
     *
     * @param string $view View name (can be dot-notation)
     * @param array $params Parameters to pass to the view
     * @param string|null $layout Layout to use (null = default layout)
     * @return string Rendered HTML content
     * @throws \Exception If view file not found
     */
    public static function make(string $view, array $params = [], ?string $layout = null): string
    {
        try {
            // Merge shared data with view-specific params
            $params = array_merge(self::$sharedData, $params);
            
            // Get view content
            $viewContent = self::getViewContent($view, false, $params);
            
            // If no layout specified, use default layout
            if ($layout === false) {
                return $viewContent;
            }
            
            // Get base content with appropriate layout
            $baseContent = self::getBaseContent($layout);
            
            // Replace content placeholder with view content
            return str_replace('{{content}}', $viewContent, $baseContent);
        } catch (\Exception $e) {
            // Handle the error
            try {
                return self::makeError('500', ['message' => $e->getMessage()]);
            } catch (\Exception $innerException) {
                // If makeError also fails, throw the original exception
                throw $e;
            }
        }
    }
    
    /**
     * Render a view without layout
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string Rendered content
     */
    public static function partial(string $view, array $params = []): string
    {
        return self::make($view, $params, false);
    }
    
    /**
     * Share data across all views
     *
     * @param string|array $key Key or array of key-value pairs
     * @param mixed $value Value (if key is string)
     * @return void
     */
    public static function share($key, $value = null): void
    {
        if (is_array($key)) {
            self::$sharedData = array_merge(self::$sharedData, $key);
        } else {
            self::$sharedData[$key] = $value;
        }
    }
    
    /**
     * Set or get current theme
     *
     * @param string|null $theme Theme name (null to get current theme)
     * @return string|null Current theme name
     */
    public static function theme(?string $theme = null): ?string
    {
        if ($theme !== null) {
            self::$theme = $theme;
        }
        
        return self::$theme;
    }
    
    /**
     * Set cache expiration time
     *
     * @param int $seconds Seconds to cache views (0 to disable)
     * @return void
     */
    public static function setCacheExpiration(int $seconds): void
    {
        self::$cacheExpiration = max(0, $seconds);
    }
    
    /**
     * Clear view cache
     *
     * @param string|null $view Specific view to clear (null for all)
     * @return void
     */
    public static function clearCache(?string $view = null): void
    {
        if ($view === null) {
            self::$viewCache = [];
        } elseif (isset(self::$viewCache[$view])) {
            unset(self::$viewCache[$view]);
        }
    }
    
    /**
     * Render error page
     *
     * @param string $error Error view name
     * @param array $params Additional parameters
     * @return string Rendered error page
     */
    public static function makeError(string $error, array $params = []): string
    {
        try {
            $viewPath = view_path() . 'errors/' . $error . '.php';
            
            // Check if error view exists
            if (!file_exists($viewPath)) {
                throw new \Exception("Error view not found: {$error}");
            }
            
            $params = array_merge(self::$sharedData, $params);
            
            // Start output buffering for error content
            ob_start();
            extract($params);
            include $viewPath;
            $errorContent = ob_get_clean();
            
            // Try to use error layout if exists
            $errorLayoutPath = view_path() . 'layouts/error.php';
            if (file_exists($errorLayoutPath)) {
                ob_start();
                include $errorLayoutPath;
                $baseContent = ob_get_clean();
            } else {
                // Fallback to default layout
                $baseContent = self::getBaseContent();
            }
            
            return str_replace('{{content}}', $errorContent, $baseContent);
        } catch (\Exception $e) {
            // Fallback to basic error display if error template fails
            return '<div style="padding:20px;border:1px solid #f44336;color:#f44336;">
                <h1>Error ' . htmlspecialchars($error) . '</h1>
                <p>An error occurred while processing your request.</p>
                ' . (isset($params['message']) ? '<p>' . htmlspecialchars($params['message']) . '</p>' : '') . '
            </div>';
        }
    }
    
    /**
     * Get base layout content
     *
     * @param string|null $layout Layout name (null for default)
     * @return string Layout content
     * @throws \Exception If layout file not found
     */
    protected static function getBaseContent(?string $layout = null): string
    {
        $layout = $layout ?: 'main';
        $layoutPath = self::getThemePath() . 'layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }
        
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
    
    /**
     * Get view content
     *
     * @param string $view View name
     * @param bool $isError Whether this is an error view
     * @param array $params Parameters to pass to the view
     * @return string View content
     * @throws \Exception If view file not found
     */
    protected static function getViewContent(string $view, bool $isError = false, array $params = []): string
    {
        // Check cache first if enabled
        $cacheKey = $view . '_' . md5(serialize($params));
        if (self::$cacheExpiration > 0 && isset(self::$viewCache[$cacheKey])) {
            $cachedData = self::$viewCache[$cacheKey];
            if ($cachedData['expires'] > time()) {
                return $cachedData['content'];
            }
        }
        
        // Build the path to the view file
        $basePath = $isError ? view_path() . 'errors/' : self::getThemePath();
        $view = self::resolveDotNotation($view, $basePath);
        
        // Check if view exists
        if (!file_exists($view)) {
            throw new \Exception("View not found: {$view}");
        }
        
        // Extract parameters to make them accessible in the view
        extract($params);
        
        // Start output buffering to capture the view content
        ob_start();
        include $view;
        $content = ob_get_clean();
        
        // Cache the result if caching is enabled
        if (self::$cacheExpiration > 0) {
            self::$viewCache[$cacheKey] = [
                'content' => $content,
                'expires' => time() + self::$cacheExpiration
            ];
        }
        
        return $content;
    }
    
    /**
     * Resolve dot notation view name to file path
     *
     * @param string $view View name in dot notation
     * @param string $basePath Base path to views
     * @return string Full path to view file
     */
    protected static function resolveDotNotation(string $view, string $basePath): string
    {
        if (str_contains($view, '.')) {
            $segments = explode('.', $view);
            $fileName = array_pop($segments);
            
            $directory = $basePath;
            foreach ($segments as $segment) {
                $directory .= $segment . '/';
            }
            
            return $directory . $fileName . '.php';
        }
        
        return $basePath . $view . '.php';
    }
    
    /**
     * Get path to current theme
     *
     * @return string Theme path
     */
    protected static function getThemePath(): string
    {
        if (self::$theme) {
            $themePath = view_path() . 'themes/' . self::$theme . '/';
            if (is_dir($themePath)) {
                return $themePath;
            }
        }
        
        return view_path();
    }
    
    /**
     * Include a subview within another view
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string View content
     */
    public static function include(string $view, array $params = []): string
    {
        return self::getViewContent($view, false, $params);
    }
    
    /**
     * Escape HTML content
     *
     * @param string $content Content to escape
     * @return string Escaped content
     */
    public static function escape(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Create a URL-friendly string
     *
     * @param string $text Text to convert
     * @return string URL-friendly string
     */
    public static function slug(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}