<?php
/**
 * 行为扩展
 * 2015-04-26
 * imbzd
 */
namespace Behavior;
use Any\Storage;
use Any\Any;
/**
 * 系统行为扩展：模板解析
 */
class ParseTemplateBehavior {

    // 行为扩展的执行入口必须是run
    public function run(&$_data){
        $engine             =   strtolower(C('TMPL_ENGINE_TYPE'));
        $_content           =   empty($_data['content'])?$_data['file']:$_data['content'];
        $_data['prefix']    =   !empty($_data['prefix'])?$_data['prefix']:C('TMPL_CACHE_PREFIX');

        // Smarty
        if(strpos($engine,'\\')){
            $class  =   $engine;
        }else{
            $class   =  'Any\\Template\\Driver\\'.ucwords($engine);                
        }            
        if(class_exists($class)) {
            $tpl   =  new $class;
            $tpl->fetch($_content,$_data['var']);
        }else {  // 类没有定义
            E(L('_NOT_SUPPORT_').': ' . $class);
        }
    }
}
