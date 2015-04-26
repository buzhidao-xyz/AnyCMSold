<?php
/**
 * Smarty模板引擎驱动 
 */
namespace Any\Template\Driver;

class Smarty {

    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile,$var) {
        $templateFile   =   substr($templateFile,strlen(THEME_PATH));
        vendor('Smarty.Smarty#class');
        $tpl            =   new \Smarty();
        $tpl->caching       = C('TMPL_CACHE_ON');
        $tpl->template_dir  = THEME_PATH;
        $tpl->compile_dir   = COMPILE_PATH;
        $tpl->cache_dir     = CACHE_PATH;
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key=>$val){
                $tpl->{$key}   =  $val;
            }
        }
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}