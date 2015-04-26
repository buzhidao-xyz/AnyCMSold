<?php
/**
 * EaseTemplate模板引擎驱动 
 */
namespace Any\Template\Driver;

class Ease {
    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile,$var) {
        $templateFile   = substr($templateFile,strlen(THEME_PATH),-5);
        $CacheDir       = substr(CACHE_PATH,0,-1);
        $TemplateDir    = substr(THEME_PATH,0,-1);
        vendor('EaseTemplate.template#ease');
        $config     =  array(
        'CacheDir'      =>  $CacheDir,
        'TemplateDir'   =>  $TemplateDir,
        'TplType'       =>  'html'
         );        
        if(C('TMPL_ENGINE_CONFIG')) {
            $config     =  array_merge($config,C('TMPL_ENGINE_CONFIG'));
        }
        $tpl = new \EaseTemplate($config);
        $tpl->set_var($var);
        $tpl->set_file($templateFile);
        $tpl->p();
    }
}