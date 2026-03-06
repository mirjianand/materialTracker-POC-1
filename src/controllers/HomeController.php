<?php
// src/controllers/HomeController.php
require_once __DIR__ . '/BaseController.php';

class HomeController extends BaseController {
    public function index() {
        $data = ['title' => 'Material Tracker - Home'];
        return $this->render('home', $data);
    }
}

?>