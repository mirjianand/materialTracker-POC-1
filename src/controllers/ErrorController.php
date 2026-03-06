<?php
// src/controllers/ErrorController.php
require_once __DIR__ . '/BaseController.php';

class ErrorController extends BaseController {
    public function forbidden() {
        return $this->render('forbidden', ['title' => 'Access Denied']);
    }
}
?>