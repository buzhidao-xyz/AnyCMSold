<?php
/**
 * TemplateLite模板引擎驱动 
 */
namespace Any\Template\Driver;

class Lite {
    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile,$var) {
        vendor("TemplateLite.class#template");
        $templateFile   =   substr($templateFile,strlen(THEME_PATH));
        $tpl            =   new \Template_Lite();
        $tpl->template_dir  = THEME_PATH;
        $tpl->compile_dir   = COMPILE_PATH;
        $tpl->cache_dir     = CACHE_PATH;
        if(C('TMPL_ENGINE_CONFIG')) {
            $config     =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key=>$val){
                $tpl->{$key}   =  $val;
            }
        }
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}