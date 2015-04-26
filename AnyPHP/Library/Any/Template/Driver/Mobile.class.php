<?php
/**
 * MobileTemplate模板引擎驱动 
 */
namespace Any\Template\Driver;

class Mobile {
    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile,$var) {
        $templateFile=substr($templateFile,strlen(THEME_PATH));
        $var['_any_template_path']=$templateFile;
        exit(json_encode($var));	
    }
}
