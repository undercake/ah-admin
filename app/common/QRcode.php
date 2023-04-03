<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-25 13:38:50
 * @LastEditTime: 2023-03-25 14:40:06
 * @FilePath: /tp6/app/common/QRcode.php
 * @Description:
 */

namespace app\common;

use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\QrCode as QR;
use Endroid\QrCode\Writer\PngWriter;

class QRcode
{
  private $qr;
  public function __construct(String $txt, int $size = 300)
  {
    $this->qr = QR::create($txt)
      ->setForegroundColor(new Color(0, 0, 0))
      ->setBackgroundColor(new Color(255, 255, 255))
      ->setSize($size);
  }

  public function print()
  {
    $writer = new PngWriter();
    $logo = Logo::create(__DIR__ . '/assets/symfony.png')
      ->setResizeToWidth(50);
    $result = $writer->write($this->qr, $logo)->getString();

    return response($result, 200, ['Content-Length' => strlen($result)])->contentType('image/png');
  }
}
