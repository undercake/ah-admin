<?php
namespace app\common;
use Overtrue\Pinyin\Converter;
use Overtrue\Pinyin\Collection;
/*
 * @Author: Undercake
 * @Date: 2023-04-02 05:38:38
 * @LastEditTime: 2023-04-02 05:43:55
 * @FilePath: /ahadmin/app/common/Pinyin.php
 * @Description: 拼音库
 */

class Pinyin
{
  public static function name(string $name, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
  {
    return self::yuToV()->surname()->withToneStyle($toneStyle)->convert($name);
  }

  public static function phrase(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
  {
    return self::yuToV()->noPunctuation()->withToneStyle($toneStyle)->convert($string);
  }

  public static function sentence(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
  {
    return self::yuToV()->withToneStyle($toneStyle)->convert($string);
  }

  public static function polyphones(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
  {
    return self::yuToV()->polyphonic()->withToneStyle($toneStyle)->convert($string);
  }

  public static function chars(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
  {
    return self::yuToV()->onlyHans()->noWords()->withToneStyle($toneStyle)->convert($string);
  }

  public static function permalink(string $string, string $delimiter = '-'): string
  {
    if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
      throw new \InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
    }

    return self::yuToV()->noPunctuation()->noTone()->convert($string)->join($delimiter);
  }

  public static function nameAbbr(string $string): Collection
  {
    return self::yuToV()->abbr($string, true);
  }

  public static function abbr(string $string, bool $asName = false): Collection
  {
    return self::yuToV()->noTone()
      ->noPunctuation()
      ->when($asName, fn ($c) => $c->surname())
      ->convert($string)
      ->map(function ($pinyin) {
        // 常用于电影名称入库索引处理，例如：《晚娘2012》-> WN2012
        return \is_numeric($pinyin) || preg_match('/\d{2,}/', $pinyin) ? $pinyin : \mb_substr($pinyin, 0, 1);
      });
  }

  public static function __callStatic(string $name, array $arguments)
  {
    $converter = Converter::make();

    if (\method_exists($converter, $name)) {
      return $converter->$name(...$arguments);
    }

    throw new \InvalidArgumentException("Method {$name} does not exist.");
  }
}
