<?php
namespace Front\Controller;

use Any\Controller;

class IndexController extends Controller
{
    public function index()
    {
    	$this->assign('msg','Front Index Page!');
    	$this->display();
    }
}