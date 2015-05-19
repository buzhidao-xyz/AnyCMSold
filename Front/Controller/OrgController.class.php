<?php
/**
 * 第三方库 逻辑控制器
 * 2015-05-05
 * imbzd
 */
namespace Front\Controller;

use Any\Controller;

class OrgController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * 生成条形码 数字 18位
     * 建议缓存redis(key-value) 使用之后或者定时（一分钟过期）销毁
     * 重复概率低
     */
    private function _GSCode()
    {
        $bcg = '39';
        $hour = date('H',TIMESTAMP)+rand(10,50);
        $ms = date('is',TIMESTAMP)+rand(1000,4000);
        $phone = str_shuffle(substr('1234567890',rand(0,6),4));
        $rand = rand(100000,999999);
        $code = $bcg.$hour.$ms.$phone.$rand;

        return $code;
    }

    /**
     * 条形码
     */
    public function barcode()
    {
        //获取code
        $code = $this->_GSCode();

        //加载基础类库
        require_once(VENDOR_PATH.'Barcodegen/class/BCGColor.php');
        require_once(VENDOR_PATH.'Barcodegen/class/BCGBarcode.php');
        require_once(VENDOR_PATH.'Barcodegen/class/BCGDrawing.php');
        require_once(VENDOR_PATH.'Barcodegen/class/BCGFontFile.php');
        //加载code128类库
        require_once(VENDOR_PATH.'Barcodegen/class/BCGcode128.barcode.php');
        
        //输出图片格式
        $filetypes = array(
            'PNG'  => \BCGDrawing::IMG_FORMAT_PNG,
            'JPEG' => \BCGDrawing::IMG_FORMAT_JPEG,
            'GIF'  => \BCGDrawing::IMG_FORMAT_GIF
        );

        //配置信息
        $className = 'BCGcode128';
        $codeconfig = array(
            'filetype' => 'PNG',
            'dpi' => '72',
            'scale' => '3',
            'rotation' => '0',
            'font_family' => 'Arial.ttf',
            'font_size' => '30',
            'text' => $code,
            'thickness' => '68',
            'start' => 'A',
            'code' => 'BCGcode128'
        );

        //画图
        $drawException = null;
        try {
            $color_black = new \BCGColor(0, 0, 0);
            $color_white = new \BCGColor(255, 255, 255);

            $code_generated = new $className();

            //设置样式
            if (isset($codeconfig['thickness'])) {
                $code_generated->setThickness(max(9, min(90, intval($codeconfig['thickness']))));
            }
            $font = 0;
            if ($codeconfig['font_family'] !== '0' && intval($codeconfig['font_size']) >= 1) {
                $font = new \BCGFontFile(VENDOR_PATH.'Barcodegen/font/'. $codeconfig['font_family'], intval($codeconfig['font_size']));
            }
            $code_generated->setFont($font);

            //编码方式A
            if (isset($codeconfig['start'])) {
                $code_generated->setStart($codeconfig['start'] === 'NULL' ? null : $codeconfig['start']);
            }

            $code_generated->setScale(max(1, min(4, $codeconfig['scale'])));
            $code_generated->setBackgroundColor($color_white);
            $code_generated->setForegroundColor($color_black);

            if ($codeconfig['text'] !== '') {
                $code = stripslashes($codeconfig['text']);
                if (function_exists('mb_convert_encoding')) {
                    $code = mb_convert_encoding($code, 'ISO-8859-1', 'UTF-8');
                }

                $code_generated->parse($code);
            }
        } catch(Exception $exception) {
            $drawException = $exception;
        }

        $drawing = new \BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setBarcode($code_generated);
            $drawing->setRotationAngle($codeconfig['rotation']);
            $drawing->setDPI($codeconfig['dpi'] === 'NULL' ? null : max(72, min(300, intval($codeconfig['dpi']))));
            $drawing->draw();
        }

        switch ($codeconfig['filetype']) {
            case 'PNG':
                header('Content-Type: image/png');
                break;
            case 'JPEG':
                header('Content-Type: image/jpeg');
                break;
            case 'GIF':
                header('Content-Type: image/gif');
                break;
        }

        $drawing->finish($filetypes[$codeconfig['filetype']]);
    }

    /**
     * 二维码
     */
    public function qrcode()
    {
        //获取code
        $code = $this->_GSCode();

        //加载类库
        require_once(VENDOR_PATH.'Qrcode/phpqrcode.php');

        //纠错级别：L、M、Q、H
        $errorCorrectionLevel = 'H';
        //点的大小：1到10
        $matrixPointSize = 12;

        //生成二维码图片资源
        $qr_image = \QRcode::png($code, false, $errorCorrectionLevel, $matrixPointSize, 2, 'GDRESOURCE');

        //logo图片
        $logo = APP_PATH.'/Public/img/app/imooly_logo_default.png';
        $logo = file_exists($logo) ? file_get_contents($logo) : '';
        $logo_image = imagecreatefromstring($logo);

        //二维码图片高宽
        $qr_image_width = imagesx($qr_image);
        $qr_image_height = imagesy($qr_image);
        //logo图高宽
        $logo_width = imagesx($logo_image);
        $logo_height = imagesy($logo_image);

        //logo图宽高以二维码图1/5比例缩放
        // $logo_qr_width = $qr_image_width/5;
        // $logo_qr_height = $logo_height*($logo_qr_width/$logo_width);

        //计算图片重组开始坐标
        $point_x = ($qr_image_width-$logo_width)/2;
        $point_y = ($qr_image_height-$logo_height)/2;

        //组合二维码图片和logo图
        imagecopyresampled($qr_image, $logo_image, $point_x, $point_y, 0, 0, $logo_width, $logo_height, $logo_width, $logo_height);

        //输出到浏览器
        Header("Content-type: image/png");
        imagepng($qr_image);

        exit;
    }
}