<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Test extends Controller
{
  
  public function test(){
  	$this->getShareBackGroubd(162);
  }
  
  public function getShareBackGroubd($uniacid){
  	$setColor = Db::name('wd_xcx_base') ->where('uniacid', $uniacid)->field('base_color, base_color2') ->find();
  	$down = $this->hex2rgb($setColor['base_color']);
  	$up = $this->hex2rgb($setColor['base_color2']);
  	
    $height = 534;
	$width = 300;

	$im = ImageCreateTrueColor($width, $height);
	//上边  162 172 254   
	//下边  119 131 234
	
	//上边： 217 237 30
	//下边： 124 212 22 根据这几个值，调整$i的系数
	//计算变化值
	$diff_r = $down['r'] - $up['r'];
	$diff_g = $down['g'] - $up['g'];
	$diff_b = $down['b'] - $up['b'];
	
	$diff_r_num = round(abs($diff_r)/530, 2);
	$diff_g_num = round(abs($diff_g)/530, 2);
	$diff_b_num = round(abs($diff_b)/530, 2);
	
	for ($i=0; $i < 534; $i++)
	{
		if($diff_r > 0){
			$diff_r_num_d = $up['r'] + floor($i * $diff_r_num);
		}else{
			$diff_r_num_d = $up['r'] - floor($i * $diff_r_num);
		}
		
		if($diff_g > 0){
			$diff_g_num_d = $up['g'] + floor($i * $diff_g_num);
		}else{
			$diff_g_num_d = $up['g'] - floor($i * $diff_g_num);
		}
		
		if($diff_b > 0){
			$diff_b_num_d = $up['b'] + floor($i * $diff_b_num);
		}else{
			$diff_b_num_d = $up['b'] - floor($i * $diff_b_num);
		}
		
		
		$Color=ImageColorAllocate($im, $diff_r_num_d, $diff_g_num_d, $diff_b_num_d);
		ImageLine($im, 0, 0+$i, $width, 0+$i, $Color);
		
	}
	
	$path = ROOT_PATH . 'public/shareImg/' .$uniacid.'_share_back.png';
	 
	//output image
	Header('Content-type: image/png');
	ImagePng($im, $path);
	 
	return $path;
  }
  
  
  private function hex2rgb($hexColor){
        $color=str_replace('#','',$hexColor);
        if (strlen($color)> 3){
            $rgb=array(
                'r'=>hexdec(substr($color,0,2)),
                'g'=>hexdec(substr($color,2,2)),
                'b'=>hexdec(substr($color,4,2))
            );
        }else{
            $r=substr($color,0,1). substr($color,0,1);
            $g=substr($color,1,1). substr($color,1,1);
            $b=substr($color,2,1). substr($color,2,1);
            $rgb=array(
                'r'=>hexdec($r),
                'g'=>hexdec($g),
                'b'=>hexdec($b)
            );
        }
        return $rgb;
    }
  
}