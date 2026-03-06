<?php
// src/controllers/BaseController.php
class BaseController {
    protected $layout = 'layout';

    protected function render($viewPath, $data = []) {
        extract($data, EXTR_SKIP);
        $viewsDir = __DIR__ . '/../views/';

        // Ensure core helpers are available to views
        require_once __DIR__ . '/../core/session.php';
        require_once __DIR__ . '/../core/csrf.php';

        // Capture view content
        $viewFile = $viewsDir . $viewPath . '.php';
        $content = '';
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
        }

        // Render layout with $content
        $layoutFile = $viewsDir . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        // Fallback: return view content
        return $content;
    }
}

?>