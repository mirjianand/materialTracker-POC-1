<?php
// src/controllers/ReportsController.php
require_once __DIR__ . '/BaseController.php';

class ReportsController extends BaseController {
    public function index() {
        return $this->render('reports/index', ['title' => 'Reports']);
    }
}

?>