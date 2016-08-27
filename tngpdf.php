<?php

if (!class_exists('TNGPDF')) {
  define('TNGPDF_VERSION', '0.1');

  include_once 'ufpdf.php';
  include_once 'version.php';

  class TNGPDF extends UFPDF
  {

    // Private properties
    var $charset;         // character set being used

    function TNGPDF($orientation = 'P', $unit = 'mm', $format = 'A4') {
      global $session_charset;

      $this->charset = $session_charset;
      UFPDF::UFPDF($orientation, $unit, $format);
    }

    function _escapetext($s) {
      if ($this->charset == 'UTF-8') {
        $s = $this->utf8_to_utf16be($s, false);
      }
      return '(' . strtr($s, [')' => '\\)', '(' => '\\(', '\\' => '\\\\']) . ')';
    }

    function GetStringWidth($s) {
      //Get width of a string in the current font
      $s = (string)$s;
      $w = 0;
      if ($this->charset == 'UTF-8') {
        $codepoints = $this->utf8_to_codepoints($s);
        $cw = &$this->CurrentFont['cw'];
        foreach ($codepoints as $cp) {
          $w += $cw[$cp];
        }
      } else {
        $cw = &$this->CurrentFont['cw'];
        $l = strlen($s);
        for ($i = 0; $i < $l; $i++) {
          $w += $cw[$s{$i}];
        }
      }
      return $w * $this->FontSize / 1000;
    }

    function Image($file, $x, $y, $w = 0, $h = 0, $type = '', $link = '', $just = '') {
      //Put an image on the page
      if (!isset($this->images[$file])) {
        //First use of image, get info
        if ($type == '') {
          $pos = strrpos($file, '.');
          if (!$pos) {
            $this->Error('Image file has no extension and no type was specified: ' . $file);
          }
          $type = substr($file, $pos + 1);
        }
        $type = strtolower($type);
        if ($type == 'jpg' || $type == 'jpeg') {
          $info = $this->_parsejpg($file);
        } elseif ($type == 'png') {
          $info = $this->_parsepng($file);
        } else {
          //Allow for additional formats
          $mtd = '_parse' . $type;
          if (!method_exists($this, $mtd)) {
            $this->Error('Unsupported image type: ' . $type);
          }
          $info = $this->$mtd($file);
        }
        $info['i'] = count($this->images) + 1;
        $this->images[$file] = $info;
      } else {
        $info = $this->images[$file];
      }
      //Automatic width and height calculation if needed
      if ($w == 0 && $h == 0) {
        //Put image at 72 dpi
        $w = $info['w'] / $this->k;
        $h = $info['h'] / $this->k;
      }
      if ($w == 0) {
        $w = $h * $info['w'] / $info['h'];
      }
      if ($h == 0) {
        $h = $w * $info['h'] / $info['w'];
      }
      if ($just == 'C') {
        $x = $this->lMargin + (($this->w - $this->lMargin - $this->rMargin) / 2) - ($w / 2);
      }
      $this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
      if ($link) {
        $this->Link($x, $y, $w, $h, $link);
      }
    }

    function WriteLongIndent($h, $txt, $link = '', $indent = 0, $rows = 0, $textheight = 0) {
      if ($textheight == 0) {
        $textheight = $h;
      }

      //Output text in flowing mode
      $cw = &$this->CurrentFont['cw'];
      $w = $this->w - $this->rMargin - $this->x - $indent;
      $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
      $s = str_replace("\r", '', $txt);
      $nb = strlen($s);
      $sep = -1;
      $i = 0;
      $j = 0;
      $l = 0;
      $nl = 1;
      while ($i < $nb) {
        //Get next character
        $c = $s{$i};
        if ($c == "\n") {
          //Explicit line break
          $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
          $i++;
          $sep = -1;
          $j = $i;
          $l = 0;
          if ($nl == 1) {
            $this->indentRowCount++;
            if ($this->indentRowCount >= $rows) {
              $indent = 0;
            }
            $this->x = $this->lMargin + $indent;
            $w = $this->w - $this->rMargin - $this->x - $startx - $indent;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
          }
          $nl++;
          continue;
        }
        if ($c == ' ') {
          $sep = $i;
        }
        $l += $cw[$c];
        if ($l > $wmax) {
          //Automatic line break
          if ($sep == -1) {
            if ($this->x > $this->lMargin) {
              $this->indentRowCount++;
              if ($this->indentRowCount >= $rows) {
                $indent = 0;
              }
              //Move to next line
              $this->x = $this->lMargin + $indent;
              $this->y += $h;
              $w = $this->w - $this->rMargin - $this->x - $indent;
              $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
              $i++;
              $nl++;
              continue;
            }
            if ($i == $j) {
              $i++;
            }
            if ($textheight != $h) {
              $this->Cell($w, $textheight, substr($s, $j, $i - $j), 0, 0, '', 0, $link);
              $this->Cell($w, $h, '', 0, 2, '', 0, $link);
            } else {
              $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
            }
          } else {
            if ($textheight != $h) {
              $this->Cell($w, $textheight, substr($s, $j, $sep - $j), 0, 0, '', 0, $link);
              $this->Cell($w, $h, '', 0, 2, '', 0, $link);
            } else {
              $this->Cell($w, $h, substr($s, $j, $sep - $j), 0, 2, '', 0, $link);
            }
            $i = $sep + 1;
          }
          $sep = -1;
          $j = $i;
          $l = 0;
          if ($nl == 1) {
            $this->indentRowCount++;
            if ($this->indentRowCount >= $rows) {
              $indent = 0;
            }
            $this->x = $this->lMargin + $indent;
            $w = $this->w - $this->rMargin - $this->x - $indent;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
          }
          $nl++;
        } else {
          $i++;
        }
      }
      //Last chunk
      if ($i != $j) {
        $this->Cell($l / 1000 * $this->FontSize, $textheight, substr($s, $j), 0, 0, '', 0, $link);
      }
    }

    function WriteHTML($html, $indent = 0, $rows = 0) {
      $this->indentRowCount = 0;
      if ($indent > 0) {
        $this->SetX($this->GetX() + $indent);
      }
      $html = str_replace("\n", '', $html);
      $a = preg_split('/<(.*)\/?>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
      $height = $this->FontSize + 0.03;
      $textheight = 0;
      foreach ($a as $i => $e) {
        if ($i % 2 == 0) {
          $this->WriteLongIndent($height, $e, '', $indent, $rows, $textheight);
        } else {
          if ($e{0} == '/') {
            $tag = strtoupper(substr($e, 1));
            $this->CloseTag($tag);
            if ($tag == 'SUP') {
              $textheight = 0;
            }
          } else {
            $a2 = explode(' ', $e);
            $tag = strtoupper(array_shift($a2));
            $attr = [];
            foreach ($a2 as $v) {
              if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                $attr[strtoupper($a3[1])] = $a3[2];
              }
            }
            $this->OpenTag($tag, $attr);
            if ($tag == 'SUP') {
              $textheight = $height / 2;
            }
          }
        }
      }
    }

    function OpenTag($tag, $attr) {
      //Opening tag
      if ($tag == 'B' or $tag == 'I' or $tag == 'U') {
        $this->SetStyle($tag, true);
      }
      if ($tag == 'A') {
        $this->HREF = $attr['HREF'];
      }
      if ($tag == 'SUP') {
        $this->SetFontSize($this->FontSizePt - 4);
      }
      if ($tag == 'BR') {
        $this->Ln($this->FontSize + 0.03);
      }
    }

    function CloseTag($tag) {
      //Closing tag
      if ($tag == 'B' or $tag == 'I' or $tag == 'U') {
        $this->SetStyle($tag, false);
      }
      if ($tag == 'SUP') {
        $this->SetFontSize($this->FontSizePt + 4);
      }
      if ($tag == 'A') {
        $this->HREF = '';
      }
      if ($tag == 'P') {
        $this->Ln($this->FontSize + 0.03);
      }
    }

    function SetStyle($tag, $enable) {
      //Modify style and select corresponding font
      $this->$tag += ($enable ? 1 : -1);
      $style = '';
      foreach (['B', 'I', 'U'] as $s) {
        if ($this->$s > 0) {
          $style .= $s;
        }
      }
      $this->SetFont('', $style);
    }

    function SetFont($family, $style = '', $size = 0) {
      //Select a font; size given in points
      $family = strtolower($family);
      if ($family == '') {
        $family = $this->FontFamily;
      }
      $style = strtoupper($style);
      if (strpos($style, 'U') !== false) {
        $this->underline = true;
        $style = str_replace('U', '', $style);
      } else {
        $this->underline = false;
      }
      if ($style == 'IB') {
        $style = 'BI';
      }
      if ($size == 0) {
        $size = $this->FontSizePt;
      }
      //Test if font is already selected
      if ($this->FontFamily == $family && $this->FontStyle == $style && $this->FontSizePt == $size) {
        return;
      }
      //Test if used for the first time
      $fontkey = $family . $style;
      if (!isset($this->fonts[$fontkey])) {
        $this->AddFont($family, $style);
      }
      //Select it
      $this->FontFamily = $family;
      $this->FontStyle = $style;
      $this->FontSizePt = $size;
      $this->FontSize = $size / $this->k;
      $this->CurrentFont = &$this->fonts[$fontkey];
      if ($this->page > 0) {
        $this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
      }
    }

    function AddFont($family, $style = '', $file = '') {
      //Add a TrueType or Type1 font
      $family = strtolower($family);
      $style = strtoupper($style);
      if ($style == 'IB') {
        $style = 'BI';
      }
      $subdir = '';
      if ($this->charset == 'UTF-8') {
        $subdir = '/utf8';
      }
      // don't fail here, just return
      if (isset($this->fonts[$family . $style])) {
        return;
      }
      if ($file == '') {
        $file = str_replace(' ', '', $family) . strtolower($style) . '.php';
      }
      // if a style is available, revert to the regular font
      if (!is_file($this->_getfontpath() . $family . $subdir . '/' . $file)) {
        $file = str_replace(' ', '', $family) . '.php';
      }
      include $this->_getfontpath() . $family . $subdir . '/' . $file;
      if (!isset($name)) {
        $this->Error('Could not include font definition file: ' . $file);
      }
      $i = count($this->fonts) + 1;
      if ($type == 'core') {
        $this->fonts[$family . $style] = ['i' => $i, 'type' => $type, 'family' => $family, 'name' => $name, 'up' => $up, 'ut' => $ut, 'cw' => $cw];
      } else {
        $this->fonts[$family . $style] = ['i' => $i, 'type' => $type, 'family' => $family, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'file' => $file, 'ctg' => $ctg];
      }
      if ($file) {
        if ($type == 'TrueTypeUnicode') {
          $this->FontFiles[$file] = ['family' => $family, 'length1' => $originalsize];
        } elseif ($type != 'core') {
          $this->FontFiles[$file] = ['family' => $family, 'length1' => $size1, 'length2' => $size2];
        }
      }
    }

    function GetPageSize() {
      $dim = [];
      $dim[w] = $this->w;
      $dim[h] = $this->h;
      return $dim;
    }

    function GetFontSize($font = '', $fontsize = '') {
      if ($font != '') {
        $origfamily = $this->FontFamily;
        $this->SetFont($font);
      }
      if ($fontsize != '') {
        $origsize = $this->FontSizePt;
        $this->SetFont('', '', $fontsize);
      }
      $size = $this->FontSize;
      if ($font != '') {
        $this->SetFont($origfamily);
      }
      if ($fontsize != '') {
        $this->SetFont('', '', $origsize);
      }

      return $size;
    }

    function Header() {
      global $titleConfig;

      if ($this->page == 1 && $titleConfig['skipFirst'] == 'true') {
        return;
      }
      $this->SetFont($titleConfig['font'], 'B', $titleConfig['fontSize']);
      $origlMargin = $this->lMargin;
      $this->lMargin = $titleConfig['lMargin'];
      $this->SetX($titleConfig['lMargin']);
      $this->Cell($this->w - $titleConfig['lMargin'] - $this->rMargin, $this->FontSize, $titleConfig['title'], 0, 0, $titleConfig['justification']);
      if ($titleConfig['line']) {
        $this->Line($titleConfig['lMargin'], $this->y + $this->FontSize, $this->w - $this->rMargin, $this->y + $this->FontSize);
      }
      $this->Ln($this->FontSize);
      $this->Ln($this->FontSize);

      if ($titleConfig['outline'] == true) {
        $this->Rect($this->lMargin, $this->y, $this->w - $this->lMargin - $this->rMargin, $this->h - $this->bMargin - $this->y);
      }
      // draw a title row at the start of the next page
      if ($titleConfig['header'] != false) {
        $this->SetFont($titleConfig['headerFont'], 'B', $titleConfig['headerFontSize']);
        $this->Cell(0, $this->FontSize + 0.1, $titleConfig['header'], 1, 1, 'L', 1);
        $this->Ln(0.05);
      }

      // reset our left margin
      $this->SetLeftMargin($origlMargin);
    }

    function Footer() {
      global $footerConfig;
      global $tngdomain, $sitename, $dbowner;

      if ($this->page == 1 && $footerConfig['skipFirst'] == 'true') {
        return;
      }

      $origlMargin = $this->lMargin;
      $this->SetLeftMargin($footerConfig['lMargin']);
      $this->SetFont($footerConfig['font'], '', $footerConfig['fontSizeSmall']);
      $h1 = $this->FontSize;
      $this->SetFont($footerConfig['font'], '', $footerConfig['fontSizeLarge']);
      $h2 = $this->FontSize;

      //build up our footer text
      $txt = uiTextSnippet('maintby') . ' ' . $dbowner;
      if ($sitename != '') {
        $txt = "$sitename - $txt";
      }

      $this->SetY((-1 * $footerConfig['bMargin']) - $h1 - $h2);
      $this->Cell(0, $h2, $txt, 0, 0, 'L');
      $this->Cell(0, $h2, Date('d M Y'), 0, 0, 'R');

      $this->SetFont('', '', 6);
      $this->SetY((-1 * $footerConfig['bMargin']) - $h1);
      $this->Cell(0, $h1, $tngdomain, 0, 0, 'L');
      if ($footerConfig['printWordPage'] == true) {
        $pagetext = uiTextSnippet('page') . ' ';
      }
      $pagetext .= $this->page;
      $this->Cell(0, $h1, $pagetext, 0, 0, 'R');
      if ($footerConfig['line']) {
        $this->Line($footerConfig['lMargin'], $this->y - $h1 - $h2, $this->w - $this->rMargin, $this->y - $h1 - $h2);
      }
      $this->SetLeftMargin($origlMargin);
    }

    function GetFooterHeight() {
      global $footerConfig;

      $this->SetFont($footerConfig['font'], '', $footerConfig['fontSizeSmall']);
      $h = $this->FontSize;
      $this->SetFont($footerConfig['font'], '', $footerConfig['fontSizeLarge']);
      $h += $this->FontSize;
      return $h;
    }

    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = 0, $hangind = 0) {
      //Output text with automatic or explicit line breaks
      $cw = &$this->CurrentFont['cw'];
      if ($w == 0) {
        $w = $this->w - $this->rMargin - $this->x;
      }
      $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
      $s = str_replace("\r", '', $txt);
      $s = str_replace("<i>", chr(1), $s);
      $s = str_replace("</i>", chr(2), $s);
      $nb = strlen($s);
      if ($nb > 0 && $s[$nb - 1] == "\n") {
        $nb--;
      }
      $b = 0;
      if ($border) {
        if ($border == 1) {
          $border = 'LTRB';
          $b = 'LRT';
          $b2 = 'LR';
        } else {
          $b2 = '';
          if (strpos($border, 'L') !== false) {
            $b2 .= 'L';
          }
          if (strpos($border, 'R') !== false) {
            $b2 .= 'R';
          }
          $b = (strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
        }
      }
      $sep = -1;
      $i = 0;
      $j = 0;
      $l = 0;
      $ns = 0;
      $nl = 1;

      $this->Cell($w, $h / 4, "", $b, 2, $align, $fill);
      $b = $b2;
      while ($i < $nb) {
        //Get next character
        $c = $s{$i};
        if ($c == "\n") {
          //Explicit line break
          if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
          }
          $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
          if ($hangind > 0) {
            $this->SetX($hangind);
          }
          $i++;
          $sep = -1;
          $j = $i;
          $l = 0;
          $ns = 0;
          $nl++;
          //if($border && $nl==2)
          //  $b=$b2;
          continue;
        }
        if ($c == ' ') {
          $sep = $i;
          $ls = $l;
          $ns++;
        }
        //$this->FontStyle = 'I';
        if ($c == chr(1)) {
          $this->SetFont('helvetica', 'I', 10);
        } elseif ($this->charset == 'UTF-8') {
          $codepoints = $this->utf8_to_codepoints($c);
          foreach ($codepoints as $cp) {
            $l += $cw[$cp];
          }
        } else {
          $l += $cw[$c];
        }
        if ($l > $wmax) {
          //Automatic line break
          if ($sep == -1) {
            if ($i == $j) {
              $i++;
            }
            if ($this->ws > 0) {
              $this->ws = 0;
              $this->_out('0 Tw');
            }
            $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
            if ($hangind > 0) {
              $this->SetX($hangind);
            }
          } else {
            if ($align == 'J') {
              $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
              $this->_out(sprintf('%.3f Tw', $this->ws * $this->k));
            }
            $this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
            if ($hangind > 0) {
              $this->SetX($hangind);
            }
            $i = $sep + 1;
          }
          $sep = -1;
          $j = $i;
          $l = 0;
          $ns = 0;
          $nl++;
          if ($border && $nl == 2) {
            $b = $b2;
          }
        } else {
          $i++;
        }
      }
      //Last chunk
      if ($this->ws > 0) {
        $this->ws = 0;
        $this->_out('0 Tw');
      }
      $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
      if ($border && strpos($border, 'B') !== false) {
        $b .= 'B';
      }
      $this->Cell($w, $h / 4, "", $b, 2, $align, $fill);
      $this->x = $this->lMargin;
    }

    function CellFit($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $scale = 0, $force = 1) {

      // CellFit adjusts text with horizontal scaling if text is too wide for cell
      // CellFit developed by Patrick Benny (www.fpdf.org)
      //Get string width
      $str_width = $this->GetStringWidth($txt);

      //Calculate ratio to fit cell
      if ($w == 0) {
        $w = $this->w - $this->rMargin - $this->x;
      }
      $ratio = 0;
      if ($str_width != 0) {
        $ratio = ($w - $this->cMargin * 2) / $str_width;
      }

      $fit = ($ratio < 1 || ($ratio > 1 && $force == 1));
      if ($fit) {
        switch ($scale) {
          //Character spacing
          case 0:
            //Calculate character spacing in points
            $char_space = ($w - $this->cMargin * 2 - $str_width) / max($this->MBGetStringLength($txt) - 1, 1) * $this->k;
            //Set character spacing
            $this->_out(sprintf('BT %.2f Tc ET', $char_space));
            break;

          //Horizontal scaling
          case 1:
            //Calculate horizontal scaling
            $horiz_scale = $ratio * 100.0;
            //Set horizontal scaling
            $this->_out(sprintf('BT %.2f Tz ET', $horiz_scale));
            break;
        }
        //Override user alignment (since text will fill up cell)
        $align = '';
      }

      //Pass on to Cell method
      $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

      //Reset character spacing/horizontal scaling
      if ($fit) {
        $this->_out('BT ' . ($scale == 0 ? '0 Tc' : '100 Tz') . ' ET');
      }
    }

    function MBGetStringLength($s) {
      //MBGetStringLength is a patch that allows CJK double-byte text, by Patrick Benny (www.fpdf.org)
      if ($this->CurrentFont['type'] == 'Type0') {
        $len = 0;
        $nbbytes = strlen($s);
        for ($i = 0; $i < $nbbytes; $i++) {
          if (ord($s[$i]) < 128) {
            $len++;
          } else {
            $len++;
            $i++;
          }
        }
        return $len;
      } else {
        return strlen($s);
      }
    }

    function _puttruetypeunicode($font) {
      //Type0 Font
      $this->_newobj();
      $this->_out('<</Type /Font');
      $this->_out('/Subtype /Type0');
      $this->_out('/BaseFont /' . $font['name'] . '-UCS');
      $this->_out('/Encoding /Identity-H');
      $this->_out('/DescendantFonts [' . ($this->n + 1) . ' 0 R]');
      $this->_out('>>');
      $this->_out('endobj');

      //CIDFont
      $this->_newobj();
      $this->_out('<</Type /Font');
      $this->_out('/Subtype /CIDFontType2');
      $this->_out('/BaseFont /' . $font['name']);
      $this->_out('/CIDSystemInfo <</Registry (Adobe) /Ordering (UCS) /Supplement 0>>');
      $this->_out('/FontDescriptor ' . ($this->n + 1) . ' 0 R');
      $c = 0;
      foreach ($font['cw'] as $i => $w) {
        $widths .= $i . ' [' . $w . '] ';
      }
      $this->_out('/W [' . $widths . ']');
      $this->_out('/CIDToGIDMap ' . ($this->n + 2) . ' 0 R');
      $this->_out('>>');
      $this->_out('endobj');

      //Font descriptor
      $this->_newobj();
      $this->_out('<</Type /FontDescriptor');
      $this->_out('/FontName /' . $font['name']);
      foreach ($font['desc'] as $k => $v) {
        $s .= ' /' . $k . ' ' . $v;
      }
      if ($font['file']) {
        $s .= ' /FontFile2 ' . $this->FontFiles[$font['file']]['n'] . ' 0 R';
      }
      $this->_out($s);
      $this->_out('>>');
      $this->_out('endobj');

      //Embed CIDToGIDMap
      $this->_newobj();
      if (defined('FPDF_FONTPATH')) {
        $file = FPDF_FONTPATH . $font['family'] . '/utf8/' . $font['ctg'];
      } else {
        $file = $font['ctg'];
      }
      $size = filesize($file);
      if (!$size) {
        $this->Error('Truetype Unicode Font file not found: ' . $file);
      }
      $this->_out('<</Length ' . $size);
      if (substr($file, -2) == '.z') {
        $this->_out('/Filter /FlateDecode');
      }
      $this->_out('>>');
      $f = fopen($file, 'rb');
      $this->_putstream(fread($f, $size));
      fclose($f);
      $this->_out('endobj');
    }

    function _putinfo() {
      global $tng_title, $tng_version;

      $this->_out('/Producer ' . $this->_textstring("$tng_title, v.$tng_version"));
      if (!empty($this->title)) {
        $this->_out('/Title ' . $this->_textstring($this->title));
      }
      if (!empty($this->subject)) {
        $this->_out('/Subject ' . $this->_textstring($this->subject));
      }
      if (!empty($this->author)) {
        $this->_out('/Author ' . $this->_textstring($this->author));
      }
      if (!empty($this->keywords)) {
        $this->_out('/Keywords ' . $this->_textstring($this->keywords));
      }
      if (!empty($this->creator)) {
        $this->_out('/Creator ' . $this->_textstring($this->creator));
      }
      $this->_out('/CreationDate ' . $this->_textstring('D:' . date('YmdHis')));
    }

    function _putfonts() {
      $nf = $this->n;
      foreach ($this->diffs as $diff) {
        //Encodings
        $this->_newobj();
        $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $diff . ']>>');
        $this->_out('endobj');
      }
      foreach ($this->FontFiles as $file => $info) {
        //Font file embedding
        $this->_newobj();
        $this->FontFiles[$file]['n'] = $this->n;
        $font = '';
        $charset_dir = '';
        if ($this->charset == 'UTF-8') {
          $charset_dir = '/utf8';
        }
        $f = fopen($this->_getfontpath() . $info['family'] . $charset_dir . '/' . $file, 'rb', 1);
        if (!$f) {
          $this->Error('Font file not found: ' . $file);
        }
        while (!feof($f)) {
          $font .= fread($f, 8192);
        }
        fclose($f);
        $compressed = (substr($file, -2) == '.z');
        if (!$compressed && isset($info['length2'])) {
          $header = (ord($font{0}) == 128);
          if ($header) {
            //Strip first binary header
            $font = substr($font, 6);
          }
          if ($header && ord($font{$info['length1']}) == 128) {
            //Strip second binary header
            $font = substr($font, 0, $info['length1']) . substr($font, $info['length1'] + 6);
          }
        }
        $this->_out('<</Length ' . strlen($font));
        if ($compressed) {
          $this->_out('/Filter /FlateDecode');
        }
        $this->_out('/Length1 ' . $info['length1']);
        if (isset($info['length2'])) {
          $this->_out('/Length2 ' . $info['length2'] . ' /Length3 0');
        }
        $this->_out('>>');
        $this->_putstream($font);
        $this->_out('endobj');
      }
      foreach ($this->fonts as $k => $font) {
        //Font objects
        $this->fonts[$k]['n'] = $this->n + 1;
        $type = $font['type'];
        $name = $font['name'];
        if ($type == 'core') {
          //Standard font
          $this->_newobj();
          $this->_out('<</Type /Font');
          $this->_out('/BaseFont /' . $name);
          $this->_out('/Subtype /Type1');
          if ($name != 'Symbol' && $name != 'ZapfDingbats') {
            $this->_out('/Encoding /WinAnsiEncoding');
          }
          $this->_out('>>');
          $this->_out('endobj');
        } elseif ($type == 'Type1' || $type == 'TrueType') {
          //Additional Type1 or TrueType font
          $this->_newobj();
          $this->_out('<</Type /Font');
          $this->_out('/BaseFont /' . $name);
          $this->_out('/Subtype /' . $type);
          $this->_out('/FirstChar 32 /LastChar 255');
          $this->_out('/Widths ' . ($this->n + 1) . ' 0 R');
          $this->_out('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
          if ($font['enc']) {
            if (isset($font['diff'])) {
              $this->_out('/Encoding ' . ($nf + $font['diff']) . ' 0 R');
            } else {
              $this->_out('/Encoding /WinAnsiEncoding');
            }
          }
          $this->_out('>>');
          $this->_out('endobj');
          //Widths
          $this->_newobj();
          $cw = &$font['cw'];
          $s = '[';
          for ($i = 32; $i <= 255; $i++) {
            $s .= $cw[chr($i)] . ' ';
          }
          $this->_out($s . ']');
          $this->_out('endobj');
          //Descriptor
          $this->_newobj();
          $s = '<</Type /FontDescriptor /FontName /' . $name;
          foreach ($font['desc'] as $k => $v) {
            $s .= ' /' . $k . ' ' . $v;
          }
          $file = $font['file'];
          if ($file) {
            $s .= ' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$file]['n'] . ' 0 R';
          }
          $this->_out($s . '>>');
          $this->_out('endobj');
        } else {
          //Allow for additional types
          $mtd = '_put' . strtolower($type);
          if (!method_exists($this, $mtd)) {
            $this->Error('Unsupported font type: ' . $type);
          }
          $this->$mtd($font);
        }
      }
    }

  }

  // end of class
}
