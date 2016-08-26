<?php
/**
 * 将指定标签内的字符转化为HTML实体
 * 
 * @package PreTransformer
 * @author Wis Chu
 * @version 2.2.0
 * @link https://wischu.com/
 */
class PreTransformer_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('PreTransformer_Plugin', 'parse');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
		$PreTranTag = new Typecho_Widget_Helper_Form_Element_Text('PreTranTag', NULL, 'pre code', '识别标签', _t('填写插件需要识别并处理其中内容的规则，两个不同规则之间用英文逗号 , 分隔，不需 &gt; 及 &lt; 符号。<br />如需识别带指定样式的标签，请在标签名后用英文句号 . 连接样式名。<br /><br />例1: <strong>pre</strong> [处理 &lt;pre&gt;( xxx )&lt;/pre&gt;]<br />例2: <strong>pre code</strong> [处理 &lt;pre&gt;&lt;code&gt;( xxx )&lt;/code&gt;&lt;/pre&gt;]<br />例3: <strong>pre.blush</strong> [处理&lt;pre class="blush"&gt;( xxx )&lt;/pre&gt;]<br />例4: <strong>pre code.blush</strong> [处理&lt;pre&gt;&lt;code class="blush"&gt;( xxx )&lt;/code&gt;&lt;/pre&gt;]'));
		$form->addInput($PreTranTag);
    }
    
    /**
     * 个人用户的配置面板
     * *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * *
     * @access public
     * @return void
     */
    public static function parse($text = '', $widget, $lastResult)
    {
		$text = empty($lastResult) ? $text : $lastResult;

		$opt = Typecho_Widget::widget('Widget_Options')->plugin('PreTransformer')->PreTranTag;
		$OptTags = explode(',',$opt);
		foreach($OptTags as $EachTag){
			if(!empty($EachTag)){
				$EachDiv = explode(' ', $EachTag);
				$EachBeg = '';
				$EachEnd = '';
				//~ 构建开始标签表达式
				foreach($EachDiv as $ev){
					//~ 对指定样式的规则进行针对处理
					if(preg_match("/(.*?)\.(.*?)$/s", $ev, $match)){
						$EachBeg .= '<'. $match[1] .'[^>]*class="'. $match[2] .'"[^>]*>';
					}else{
						$EachBeg .= '<'. $ev .'[^>]*>';
					}
				}
				//~ 倒序构建结束标签
				krsort($EachDiv);
				foreach($EachDiv as $ev){
					$dot = strpos($ev, '.');
					$ev = $dot != false ? substr($ev, 0, $dot) : $ev;
					$EachEnd .= '<\/'. $ev .'>';
				}
				//~ 回调函数
				$filter_function = '$matches[2] = htmlspecialchars(str_replace(array(\'<br />\'."\r\n", \'<br />\'."\n"), array("\n", "\n"), $matches[2])); return $matches[1].$matches[2].$matches[3];';
				$text = preg_replace_callback("/({$EachBeg})(.*?)({$EachEnd})/s", create_function('$matches', $filter_function),  $text);
			}
		}
		return $text;
    }
}
