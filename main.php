<?php
/**
 * Plugin Name: UEditor-bmqy
 * Plugin URI: http://www.shuxinfeng.cn/sxf/4424.html
 * Description: 百度开源富文本编辑器UEditor for wordpress！此插件最早由taoqili开发，SamLiu改进,两位作者均不再发布更新版本，大山在其基础上更新到1.4.3版本，bmqy沿用更新至1.4.3.3。
 * Version: 1.4.3.3
 * Author: 大山, SamLiu, taoqili, bmqy
 * Author URI: https://www.bmqy.net
 */
@include_once( dirname( __FILE__ ) . "/ueditor.class.php" );
if ( class_exists( "UEditor" ) ) {
    $ueditor_lang = 'en';
    if( stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh-cn') !== false){
        $ueditor_lang = 'zh-cn';
    }
    $ue = new UEditor("postdivrich",array(
        //此处可以配置编辑器的所有配置项，配置方法同editor_config.js        
        "toolbars"=>array(
        	array('fullscreen', 'source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall'),
            array('customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'insertcode', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|',
            'indent', 'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'template', 'background'),
        	array('link', 'unlink', 'anchor', '|',
            'simpleupload', 'insertimage', 'scrawl', 'insertvideo', 'music', 'attachment', 'pagebreak', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'searchreplace', 'drafts', 'help')
        )
        ,'lang'=>$ueditor_lang
        ,"focus"=>true
        ,"textarea"=>"content"
        ,"zIndex"=>1
        ,"initialFrameHeight"=>320  //初始化编辑器高度,默认320
        ,"wordCount"=>false          //是否开启字数统计
        ,"autoHeightEnabled"=>false  // 是否自动长高,默认true
        //是否可以拉伸长高,默认true(当开启时，自动长高失效)
        ,"scaleEnabled"=>true
        //浮动时工具栏距离浏览器顶部的高度，用于某些具有固定头部的页面
        ,"topOffset"=>32
        ,"minFrameHeight"=>320  //编辑器拖动时最小高度,默认220
        ,"initialStyle"=>'p{font-size:14px;line-height:1.8;}'//编辑器层级的基数,可以用来改变字体等
        ,"catchRemoteImageEnable"=>false //设置是否抓取远程图片
    ));
    register_activation_hook( __FILE__, array(  &$ue, 'ue_closeDefaultEditor' ) );
    register_deactivation_hook( __FILE__, array(  &$ue, 'ue_openDefaultEditor' ) );
    add_action("wp_head",array(&$ue,'ue_importSyntaxHighlighter'));
    add_action("wp_footer",array(&$ue,'ue_syntaxHighlighter'));
    add_action("admin_head",array(&$ue,'ue_importUEditorResource'));
    add_action('edit_form_advanced', array(&$ue, 'ue_renderUEditor'));
    add_action('edit_page_form', array(&$ue, 'ue_renderUEditor'));
    add_action( 'plugins_unload', array(&$ue, 'ue_openDefaultEditor'));

    add_filter('the_editor', 'enable_ueditor');
}
function enable_ueditor($editor_box){
    if( strpos($editor_box, 'wp-content-editor-container') > 0 ){
        $js=<<<js_enable_ueditor
        <script type="text/javascript">
                var ueditor_container = document.getElementById('postdivrich');
                var editor_content = document.getElementById('content');
                var ueditor_content_container = document.createElement('script');
                var wp_ueditor_content = editor_content.defaultValue;
                ueditor_container.appendChild(ueditor_content_container);
                ueditor_content_container.setAttribute('id', 'postdivrich');
                ueditor_content_container.setAttribute('class', 'postarea');
                ueditor_content_container.setAttribute('type', 'text/plain');
                ueditor_container.removeAttribute('id');
                ueditor_container.removeAttribute('class');
                var mce_container = document.getElementById("wp-content-wrap");
                mce_container.parentNode.removeChild(mce_container);
        </script>
js_enable_ueditor;
        return $editor_box.$js;
    }
    return $editor_box;
}

function UEditorAjaxGetHandler(){
    include_once( dirname( __FILE__ ) . "/ueditor/php/imageManager.php" );
    exit;
}
add_action( 'wp_ajax_ueditor_get', 'UEditorAjaxGetHandler' );

// Should return an array in the style of array( 'ext' => $ext, 'type' => $type, 'proper_filename' => $proper_filename )
function ueditor_mime_types($mime_types ){
    $types = array(
        'apk' => 'application/android binary'
    );
    return array_merge($types, $mime_types);
}
add_filter( 'mime_types', 'ueditor_mime_types' );

function UEditorAjaxPostHandler(){
    switch($_REQUEST['method']){
        case 'imageUp':
            include_once( dirname( __FILE__ ) . "/ueditor/php/imageUp.php" );
            break;
        case 'scrawlUp':
            include_once( dirname( __FILE__ ) . "/ueditor/php/scrawlUp.php" );
            break;
        case 'fileUp':
            include_once( dirname( __FILE__ ) . "/ueditor/php/fileUp.php" );
            break;
        case 'getRemoteImage':
            include_once( dirname( __FILE__ ) . "/ueditor/php/getRemoteImage.php" );
            break;
        case 'wordImage':
            include_once( dirname( __FILE__ ) . "/ueditor/php/wordImage.php" );
            break;
        case 'onekey':
            include_once( dirname( __FILE__ ) . "/ueditor/php/onekeyUp.php" );
            break;
        default:
            break;
    }
    exit;
}
add_action( 'wp_ajax_ueditor_post', 'UEditorAjaxPostHandler' );

?>
