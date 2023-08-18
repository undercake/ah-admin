<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-25 13:38:50
 * @LastEditTime: 2023-07-31 09:15:16
 * @FilePath: /ahadmin/app/common/QRcode.php
 * @Description:
 */

namespace app\common;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class QRcode
{
  private $qr;
  private $size;
  public function __construct(String $txt, int $size = 300)
  {
    $this->qr = Builder::create()
      ->writer(new PngWriter())
      ->writerOptions([])
      ->data($txt)
      ->encoding(new Encoding('UTF-8'))
      ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
      ->size($size)
      ->margin(10)
      ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
      ->validateResult(false);
    $this->size = $size;
    return $this;
  }

  function logo(string $url) {
    $this->qr = $this->qr
      ->logoResizeToWidth($this->size / 5)
      ->logoPunchoutBackground(true)
      ->logoPath($url);
      return $this;
  }

  public function print()
  {
    $result = $this->qr->build();
    $rs_string = $result->getString();
    return response(
      $rs_string,
      200,
      ['Content-Length' => strlen($rs_string)]
      )->contentType($result->getMimeType());
  }
}
