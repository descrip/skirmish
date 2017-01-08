<?php

namespace Controllers;

class HomeController extends Controller {

    public function home($f3, $params) {
        $f3->mset([
            'title' => 'Home',
            'content' => $f3->get('THEME') . '/views/home.html',
            'readme' => file_get_contents('README.md')
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

}
