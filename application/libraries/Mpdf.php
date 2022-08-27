<?php


use Mpdf\Cache;
use Mpdf\Color\ColorConverter;
use Mpdf\Color\ColorModeConverter;
use Mpdf\Color\ColorSpaceRestrictor;
use Mpdf\Fonts\FontCache;
use Mpdf\Fonts\FontFileFinder;
use Mpdf\Otl;
use Mpdf\SizeConverter;
use Psr\Log\NullLogger;
use Mpdf\Fonts\MetricsGenerator;

class mpdf extends Mpdf\Mpdf
{
    /**
     * @var ColorConverter
     */
    private $colorConverter;

    private $tmpOrigFile;
    private $tmpConvertedFile;

    /**
     * @var Otl
     */
    private $otl;

    /**
     * @var \Mpdf\Fonts\FontCache
     */
    private $fontCache;

    /**
     * @var \Mpdf\SizeConverter
     */
    private $sizeConverter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Mpdf\Fonts\FontFileFinder
     */
    private $fontFileFinder;

    /**
     * @var string
     */
    private $fontDescriptor;

    public function __construct($conf = [])
    {
        $config = array_merge([
            'tempDir' => sys_get_temp_dir(),// . '/mpdf',
            'curlAllowUnsafeSslRequests' => true,
            'curlFollowLocation' => true,
            'margBuffer' => 0,
            'setAutoTopMargin' => false,
            'setAutoBottomMargin' => false,
            'autoMarginPadding' => 0,
            'annotMargin' => 0,
            'format' => 'letter',
            'orientation' => 'P',
            'fontDir' => FCPATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'mpdf' . DIRECTORY_SEPARATOR . 'mpdf' . DIRECTORY_SEPARATOR . 'ttfonts',
        ], $conf);
        parent::__construct($config);
        $this->noImageFile = null;
        $cmc = new ColorModeConverter();
        $csr = new ColorSpaceRestrictor($this, $cmc, $this->restrictColorSpace);
        $this->logger = new NullLogger();
        $this->colorConverter = new ColorConverter($this, $cmc, $csr);
        $this->sizeConverter = new SizeConverter($this->dpi, $this->default_font_size, $this, $this->logger);
        $this->fontCache = new FontCache(new Cache($config['tempDir'] . '/ttfontdata'));
        $this->otl = new Otl($this, $this->fontCache);
    }

    public function Thumbnail($file, $npr = 3, $spacing = 10, $x = false, $y = false)
    {
        $file = $this->PDFConverter($file);

        if(!$file)
            return false;

        $ref = new ReflectionObject($this);
        $prop = $ref->getProperty('colorConverter');
        $prop->setAccessible(true);
        // $npr = number per row
        $w = (($this->pgwidth + $spacing) / $npr) - $spacing;
        $oldlinewidth = $this->LineWidth;
        $this->SetLineWidth(0.02);
        $this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
        $h = 0;
        $maxh = 0;
        if(!$x) {
            $x = $_x = $this->lMargin;
        } else {
            $w -= $x / $npr;
            $_x = $x;
        }
        if(!$y) {
            $_y = $this->tMargin;
            if ($this->y == 0) {
                $y = $_y;
            } else {
                $y = $this->y;
            }
        } else {
            $_y = $y;
        }

        $pagecount = $this->setSourceFile($file);

        for ($n = 1; $n <= $pagecount; $n++) {
            $tplidx = $this->importPage($n);
            $size = $this->useTemplate($tplidx, $x, $y, $w);
            $this->Rect($x, $y, $size['width'], $size['height']);
            $h = max($h, $size['height']);
            $maxh = max($h, $maxh);

            if ($n % $npr == 0) {
                if (($y + $h + $spacing + $maxh) > $this->PageBreakTrigger && $n != $pagecount) {
                    $this->AddPage();
                    $x = $_x;
                    $y = $_y;
                } else {
                    $y += $h + $spacing;
                    $x = $_x;
                    $h = 0;
                }
            } else {
                $x += $w + $spacing;
            }
        }
        $this->SetLineWidth($oldlinewidth);
        $this->_clearTmpPDFConverter();
    }

    public function PDFConverter($file, $path = null) {
        $str = stream_get_contents($file, 50, 0);

        if(preg_match('/^\%PDF-([0-9]+(\.[0-9]{1,2})).%/is', $str, $matches) && $matches[1] > 1.4) {

            if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                return false;

            $file = $this->_convertPdfFile($file);
        }
        try {
            $this->getPdfReader($this->getPdfReaderId($file))->getParser()->getCrossReference();
        } catch (setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            $file = $this->_convertPdfFile($file);
        } catch (Exception $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        return $file;
    }

    private function _convertPdfFile($file) {
        $this->tmpOrigFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.pdf';
        $this->tmpConvertedFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.pdf';

        $fp = fopen($this->tmpOrigFile, 'w+');
        fwrite($fp, stream_get_contents($file, -1, 0));
        fclose($fp);

        @exec("gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sOutputFile=$this->tmpConvertedFile $this->tmpOrigFile", $out, $return);

        return fopen($this->tmpConvertedFile, 'r');
    }

    private function _clearTmpPDFConverter() {
        if($this->tmpOrigFile && file_exists($this->tmpOrigFile)) {
            @unlink($this->tmpOrigFile);
            $this->tmpOrigFile = null;
        }
        if($this->tmpConvertedFile && file_exists($this->tmpConvertedFile)) {
            @unlink($this->tmpConvertedFile);
            $this->tmpConvertedFile = null;
        }
    }

    function _setBorderLine($b, $k = 1)
    {
        $this->SetLineWidth($b['w'] / $k);
        $this->SetDColor($b['c']);
        if (empty($b['c'])) {
            return;
        }
        if ($b['c'][0] == 5) { // RGBa
            $this->SetAlpha(ord($b['c'][4]) / 100, 'Normal', false, 'S'); // mPDF 5.7.2
        } elseif ($b['c'][0] == 6) { // CMYKa
            $this->SetAlpha(ord($b['c'][5]) / 100, 'Normal', false, 'S'); // mPDF 5.7.2
        }
    }

    function printbuffer($arrayaux, $blockstate = 0, $is_table = false, $table_draft = false, $cell_dir = '')
    {
        // $blockstate = 0;	// NO margins/padding
        // $blockstate = 1;	// Top margins/padding only
        // $blockstate = 2;	// Bottom margins/padding only
        // $blockstate = 3;	// Top & bottom margins/padding
        $this->spanbgcolorarray = '';
        $this->spanbgcolor = false;
        $this->spanborder = false;
        $this->spanborddet = [];
        $paint_ht_corr = 0;
        /* -- CSS-FLOAT -- */
        if (count($this->floatDivs)) {
            list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
            if (($this->blk[$this->blklvl]['inner_width'] - $l_width - $r_width) < (2 * $this->GetCharWidth('W',
                        false))) {
                // Too narrow to fit - try to move down past L or R float
                if ($l_max < $r_max && ($this->blk[$this->blklvl]['inner_width'] - $r_width) > (2 * $this->GetCharWidth('W',
                            false))) {
                    $this->ClearFloats('LEFT', $this->blklvl);
                } elseif ($r_max < $l_max && ($this->blk[$this->blklvl]['inner_width'] - $l_width) > (2 * $this->GetCharWidth('W',
                            false))) {
                    $this->ClearFloats('RIGHT', $this->blklvl);
                } else {
                    $this->ClearFloats('BOTH', $this->blklvl);
                }
            }
        }
        /* -- END CSS-FLOAT -- */
        $bak_y = $this->y;
        $bak_x = $this->x;
        $align = '';
        if (!$is_table) {
            if (isset($this->blk[$this->blklvl]['align']) && $this->blk[$this->blklvl]['align']) {
                $align = $this->blk[$this->blklvl]['align'];
            }
            // Block-align is set by e.g. <.. align="center"> Takes priority for this block but not inherited
            if (isset($this->blk[$this->blklvl]['block-align']) && $this->blk[$this->blklvl]['block-align']) {
                $align = $this->blk[$this->blklvl]['block-align'];
            }
            if (isset($this->blk[$this->blklvl]['direction'])) {
                $blockdir = $this->blk[$this->blklvl]['direction'];
            } else {
                $blockdir = "";
            }
            $this->divwidth = $this->blk[$this->blklvl]['width'];
        } else {
            $align = $this->cellTextAlign;
            $blockdir = $cell_dir;
        }
        $oldpage = $this->page;

        // ADDED for Out of Block now done as Flowing Block
        if ($this->divwidth == 0) {
            $this->divwidth = $this->pgwidth;
        }

        if (!$is_table) {
            $this->SetLineHeight($this->FontSizePt, $this->blk[$this->blklvl]['line_height']);
        }
        $this->divheight = $this->lineheight;
        $old_height = $this->divheight;

        // As a failsafe - if font has been set but not output to page
        if (!$table_draft) {
            $this->SetFont($this->default_font, '', $this->default_font_size, true, true); // force output to page
        }

        $this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, true, $blockdir,
            $table_draft);

        $array_size = count($arrayaux);

        // Added - Otherwise <div><div><p> did not output top margins/padding for 1st/2nd div
        if ($array_size == 0) {
            $this->finishFlowingBlock(true);
        } // true = END of flowing block
        // mPDF 6
        // ALL the chunks of textbuffer need to have at least basic OTLdata set
        // First make sure each element/chunk has the OTLdata for Bidi set.
        for ($i = 0; $i < $array_size; $i++) {
            if (empty($arrayaux[$i][18])) {
                if (substr($arrayaux[$i][0], 0, 3) == "\xbb\xa4\xac") { // object identifier has been identified!
                    $unicode = [0xFFFC]; // Object replacement character
                } else {
                    $unicode = $this->UTF8StringToArray($arrayaux[$i][0], false);
                }
                $is_strong = false;
                $this->getBasicOTLdata($arrayaux[$i][18], $unicode, $is_strong);
            }
            // Gets messed up if try and use core fonts inside a paragraph of text which needs to be BiDi re-ordered or OTLdata set
            if (($blockdir == 'rtl' || $this->biDirectional) && isset($arrayaux[$i][4]) && in_array($arrayaux[$i][4],
                    ['ccourier', 'ctimes', 'chelvetica', 'csymbol', 'czapfdingbats'])) {
                throw new \Mpdf\MpdfException("You cannot use core fonts in a document which contains RTL text.");
            }
        }
        // mPDF 6
        // Process bidirectional text ready for bidi-re-ordering (which is done after line-breaks are established in WriteFlowingBlock etc.)
        if (($blockdir == 'rtl' || $this->biDirectional) && !$table_draft) {
            if (empty($this->otl)) {
                $this->otl = new Otl($this, $this->fontCache);
            }
            $this->otl->bidiPrepare($arrayaux, $blockdir);
            $array_size = count($arrayaux);
        }


        // Remove empty items // mPDF 6
        for ($i = $array_size - 1; $i > 0; $i--) {
            if (empty($arrayaux[$i][0]) && (isset($arrayaux[$i][16]) && $arrayaux[$i][16] !== '0') && empty($arrayaux[$i][7])) {
                unset($arrayaux[$i]);
            }
        }

        // Correct adjoining borders for inline elements
        if (isset($arrayaux[0][16])) {
            $lastspanborder = $arrayaux[0][16];
        } else {
            $lastspanborder = false;
        }
        for ($i = 1; $i < $array_size; $i++) {
            if (isset($arrayaux[$i][16]) && $arrayaux[$i][16] == $lastspanborder &&
                ((!isset($arrayaux[$i][9]['bord-decoration']) && !isset($arrayaux[$i - 1][9]['bord-decoration'])) ||
                    (isset($arrayaux[$i][9]['bord-decoration']) && isset($arrayaux[$i - 1][9]['bord-decoration']) && $arrayaux[$i][9]['bord-decoration'] == $arrayaux[$i - 1][9]['bord-decoration'])
                )
            ) {
                if (isset($arrayaux[$i][16]['R'])) {
                    $lastspanborder = $arrayaux[$i][16];
                } else {
                    $lastspanborder = false;
                }
                $arrayaux[$i][16]['L']['s'] = 0;
                $arrayaux[$i][16]['L']['w'] = 0;
                $arrayaux[$i - 1][16]['R']['s'] = 0;
                $arrayaux[$i - 1][16]['R']['w'] = 0;
            } else {
                if (isset($arrayaux[$i][16]['R'])) {
                    $lastspanborder = $arrayaux[$i][16];
                } else {
                    $lastspanborder = false;
                }
            }
        }

        for ($i = 0; $i < $array_size; $i++) {
            // COLS
            $oldcolumn = $this->CurrCol;
            $vetor = isset($arrayaux[$i]) ? $arrayaux[$i] : null;
            if ($i == 0 && $vetor[0] != "\n" && !$this->ispre) {
                $vetor[0] = ltrim($vetor[0]);
                if (!empty($vetor[18])) {
                    $this->otl->trimOTLdata($vetor[18], true, false);
                } // *OTL*
            }

            // FIXED TO ALLOW IT TO SHOW '0'
            if (!is_array($vetor) || (empty($vetor[0]) && !($vetor[0] === '0') && empty($vetor[7]))) { // Ignore empty text and not carrying an internal link
                // Check if it is the last element. If so then finish printing the block
                if ($i == ($array_size - 1)) {
                    $this->finishFlowingBlock(true);
                } // true = END of flowing block
                continue;
            }


            // Activating buffer properties
            if (isset($vetor[11]) && $vetor[11] != '') {   // Font Size
                if ($is_table && $this->shrin_k) {
                    $this->SetFontSize($vetor[11] / $this->shrin_k, false);
                } else {
                    $this->SetFontSize($vetor[11], false);
                }
            }

            if (isset($vetor[17]) && !empty($vetor[17])) { // TextShadow
                $this->textshadow = $vetor[17];
            }
            if (isset($vetor[16]) && !empty($vetor[16])) { // Border
                $this->spanborddet = $vetor[16];
                $this->spanborder = true;
            }

            if (isset($vetor[15])) {   // Word spacing
                $this->wSpacingCSS = $vetor[15];
                if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') {
                    $this->minwSpacing = $this->sizeConverter->convert($this->wSpacingCSS,
                            $this->FontSize) / $this->shrin_k; // mPDF 5.7.3
                }
            }
            if (isset($vetor[14])) {   // Letter spacing
                $this->lSpacingCSS = $vetor[14];
                if (($this->lSpacingCSS || $this->lSpacingCSS === '0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
                    $this->fixedlSpacing = $this->sizeConverter->convert($this->lSpacingCSS,
                            $this->FontSize) / $this->shrin_k; // mPDF 5.7.3
                }
            }


            if (isset($vetor[10]) and !empty($vetor[10])) { // Background color
                $this->spanbgcolorarray = $vetor[10];
                $this->spanbgcolor = true;
            }
            if (isset($vetor[9]) and !empty($vetor[9])) { // Text parameters - Outline + hyphens
                $this->textparam = $vetor[9];
                $this->SetTextOutline($this->textparam);
                // mPDF 5.7.3  inline text-decoration parameters
                if ($is_table && $this->shrin_k) {
                    if (isset($this->textparam['text-baseline'])) {
                        $this->textparam['text-baseline'] /= $this->shrin_k;
                    }
                    if (isset($this->textparam['decoration-baseline'])) {
                        $this->textparam['decoration-baseline'] /= $this->shrin_k;
                    }
                    if (isset($this->textparam['decoration-fontsize'])) {
                        $this->textparam['decoration-fontsize'] /= $this->shrin_k;
                    }
                }
            }
            if (isset($vetor[8])) {  // mPDF 5.7.1
                $this->textvar = $vetor[8];
            }
            if (isset($vetor[7]) and $vetor[7] != '') { // internal target: <a name="anyvalue">
                $ily = $this->y;
                if ($this->table_rotate) {
                    $this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "tbrot" => true];
                } elseif ($this->kwt) {
                    $this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "kwt" => true];
                } elseif ($this->ColActive) {
                    $this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "col" => $this->CurrCol];
                } elseif (!$this->keep_block_together) {
                    $this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page];
                }
                if (empty($vetor[0])) { // Ignore empty text
                    // Check if it is the last element. If so then finish printing the block
                    if ($i == ($array_size - 1)) {
                        $this->finishFlowingBlock(true);
                    } // true = END of flowing block
                    continue;
                }
            }
            if (isset($vetor[5]) and $vetor[5] != '') {  // Language	// mPDF 6
                $this->currentLang = $vetor[5];
            }
            if (isset($vetor[4]) and $vetor[4] != '') {  // Font Family
                $font = $this->SetFont($vetor[4], $this->FontStyle, 0, false);
            }
            if (!empty($vetor[3])) { // Font Color
                $cor = $vetor[3];
                $this->SetTColor($cor);
            }
            if (isset($vetor[2]) and $vetor[2] != '') { // Bold,Italic styles
                $this->SetStyles($vetor[2]);
            }

            if (isset($vetor[12]) and $vetor[12] != '') { // Requested Bold,Italic
                $this->ReqFontStyle = $vetor[12];
            }
            if (isset($vetor[1]) and $vetor[1] != '') { // LINK
                if (strpos($vetor[1], ".") === false && strpos($vetor[1],
                        "@") !== 0) { // assuming every external link has a dot indicating extension (e.g: .html .txt .zip www.somewhere.com etc.)
                    // Repeated reference to same anchor?
                    while (array_key_exists($vetor[1], $this->internallink)) {
                        $vetor[1] = "#" . $vetor[1];
                    }
                    $this->internallink[$vetor[1]] = $this->AddLink();
                    $vetor[1] = $this->internallink[$vetor[1]];
                }
                $this->HREF = $vetor[1];     // HREF link style set here ******
            }

            // SPECIAL CONTENT - IMAGES & FORM OBJECTS
            // Print-out special content

            if (substr($vetor[0], 0, 3) == "\xbb\xa4\xac") { // identifier has been identified!
                $objattr = $this->_getObjAttr($vetor[0]);

                /* -- TABLES -- */
                if ($objattr['type'] == 'nestedtable') {
                    if ($objattr['nestedcontent']) {
                        $level = $objattr['level'];
                        $table = &$this->table[$level][$objattr['table']];

                        if ($table_draft) {
                            $this->y += $this->table[($level + 1)][$objattr['nestedcontent']]['h']; // nested table height
                            $this->finishFlowingBlock(false, 'nestedtable');
                        } else {
                            $cell = &$table['cells'][$objattr['row']][$objattr['col']];
                            $this->finishFlowingBlock(false, 'nestedtable');
                            $save_dw = $this->divwidth;
                            $save_buffer = $this->cellBorderBuffer;
                            $this->cellBorderBuffer = [];
                            $ncx = $this->x;
                            list($dummyx, $w) = $this->_tableGetWidth($table, $objattr['row'], $objattr['col']);
                            $ntw = $this->table[($level + 1)][$objattr['nestedcontent']]['w']; // nested table width
                            if (!$this->simpleTables) {
                                if ($this->packTableData) {
                                    list($bt, $br, $bb, $bl) = $this->_getBorderWidths($cell['borderbin']);
                                } else {
                                    $br = $cell['border_details']['R']['w'];
                                    $bl = $cell['border_details']['L']['w'];
                                }
                                if ($table['borders_separate']) {
                                    $innerw = $w - $bl - $br - $cell['padding']['L'] - $cell['padding']['R'] - $table['border_spacing_H'];
                                } else {
                                    $innerw = $w - $bl / 2 - $br / 2 - $cell['padding']['L'] - $cell['padding']['R'];
                                }
                            } elseif ($this->simpleTables) {
                                if ($table['borders_separate']) {
                                    $innerw = $w - $table['simple']['border_details']['L']['w'] - $table['simple']['border_details']['R']['w'] - $cell['padding']['L'] - $cell['padding']['R'] - $table['border_spacing_H'];
                                } else {
                                    $innerw = $w - $table['simple']['border_details']['L']['w'] / 2 - $table['simple']['border_details']['R']['w'] / 2 - $cell['padding']['L'] - $cell['padding']['R'];
                                }
                            }
                            if ($cell['a'] == 'C' || $this->table[($level + 1)][$objattr['nestedcontent']]['a'] == 'C') {
                                $ncx += ($innerw - $ntw) / 2;
                            } elseif ($cell['a'] == 'R' || $this->table[($level + 1)][$objattr['nestedcontent']]['a'] == 'R') {
                                $ncx += $innerw - $ntw;
                            }
                            $this->x = $ncx;

                            $this->_tableWrite($this->table[($level + 1)][$objattr['nestedcontent']]);
                            $this->cellBorderBuffer = $save_buffer;
                            $this->x = $bak_x;
                            $this->divwidth = $save_dw;
                        }

                        $this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false,
                            $blockdir, $table_draft);
                    }
                } else {
                    /* -- END TABLES -- */
                    if ($is_table) { // *TABLES*
                        $maxWidth = $this->divwidth;  // *TABLES*
                    } // *TABLES*
                    else { // *TABLES*
                        $maxWidth = $this->divwidth - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w']);
                    } // *TABLES*

                    /* -- CSS-IMAGE-FLOAT -- */
                    // If float (already) exists at this level
                    if (isset($this->floatmargins['R']) && $this->y <= $this->floatmargins['R']['y1'] && $this->y >= $this->floatmargins['R']['y0']) {
                        $maxWidth -= $this->floatmargins['R']['w'];
                    }
                    if (isset($this->floatmargins['L']) && $this->y <= $this->floatmargins['L']['y1'] && $this->y >= $this->floatmargins['L']['y0']) {
                        $maxWidth -= $this->floatmargins['L']['w'];
                    }
                    /* -- END CSS-IMAGE-FLOAT -- */

                    list($skipln) = $this->inlineObject($objattr['type'], '', $this->y, $objattr, $this->lMargin,
                        ($this->flowingBlockAttr['contentWidth'] / Mpdf::SCALE), $maxWidth,
                        $this->flowingBlockAttr['height'], false, $is_table);
                    //  1 -> New line needed because of width
                    // -1 -> Will fit width on line but NEW PAGE REQUIRED because of height
                    // -2 -> Will not fit on line therefore needs new line but thus NEW PAGE REQUIRED
                    $iby = $this->y;
                    $oldpage = $this->page;
                    $oldcol = $this->CurrCol;
                    if (($skipln == 1 || $skipln == -2) && !isset($objattr['float'])) {
                        $this->finishFlowingBlock(false, $objattr['type']);
                        $this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false,
                            $blockdir, $table_draft);
                    }

                    if (!$table_draft) {
                        $thispage = $this->page;
                        if ($this->CurrCol != $oldcol) {
                            $changedcol = true;
                        } else {
                            $changedcol = false;
                        }

                        // the previous lines can already have triggered page break or column change
                        if (!$changedcol && $skipln < 0 && $this->AcceptPageBreak() && $thispage == $oldpage) {
                            $this->AddPage($this->CurOrientation);

                            // Added to correct Images already set on line before page advanced
                            // i.e. if second inline image on line is higher than first and forces new page
                            if (count($this->objectbuffer)) {
                                $yadj = $iby - $this->y;
                                foreach ($this->objectbuffer as $ib => $val) {
                                    if ($this->objectbuffer[$ib]['OUTER-Y']) {
                                        $this->objectbuffer[$ib]['OUTER-Y'] -= $yadj;
                                    }
                                    if ($this->objectbuffer[$ib]['BORDER-Y']) {
                                        $this->objectbuffer[$ib]['BORDER-Y'] -= $yadj;
                                    }
                                    if ($this->objectbuffer[$ib]['INNER-Y']) {
                                        $this->objectbuffer[$ib]['INNER-Y'] -= $yadj;
                                    }
                                }
                            }
                        }

                        // Added to correct for OddEven Margins
                        if ($this->page != $oldpage) {
                            if (($this->page - $oldpage) % 2 == 1) {
                                $bak_x += $this->MarginCorrection;
                            }
                            $oldpage = $this->page;
                            $y = $this->tMargin - $paint_ht_corr;
                            $this->oldy = $this->tMargin - $paint_ht_corr;
                            $old_height = 0;
                        }
                        $this->x = $bak_x;
                        /* -- COLUMNS -- */
                        // COLS
                        // OR COLUMN CHANGE
                        if ($this->CurrCol != $oldcolumn) {
                            if ($this->directionality == 'rtl') { // *OTL*
                                $bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
                            } // *OTL*
                            else { // *OTL*
                                $bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
                            } // *OTL*
                            $this->x = $bak_x;
                            $oldcolumn = $this->CurrCol;
                            $y = $this->y0 - $paint_ht_corr;
                            $this->oldy = $this->y0 - $paint_ht_corr;
                            $old_height = 0;
                        }
                        /* -- END COLUMNS -- */
                    }

                    /* -- CSS-IMAGE-FLOAT -- */
                    if ($objattr['type'] == 'image' && isset($objattr['float'])) {
                        $fy = $this->y;

                        // DIV TOP MARGIN/BORDER/PADDING
                        if ($this->flowingBlockAttr['newblock'] && ($this->flowingBlockAttr['blockstate'] == 1 || $this->flowingBlockAttr['blockstate'] == 3) && $this->flowingBlockAttr['lineCount'] == 0) {
                            $fy += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
                        }

                        if ($objattr['float'] == 'R') {
                            $fx = $this->w - $this->rMargin - $objattr['width'] - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);
                        } elseif ($objattr['float'] == 'L') {
                            $fx = $this->lMargin + ($this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left']);
                        }
                        $w = $objattr['width'];
                        $h = abs($objattr['height']);

                        $widthLeft = $maxWidth - ($this->flowingBlockAttr['contentWidth'] / Mpdf::SCALE);
                        $maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10);
                        // For Images
                        $extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left'] + $objattr['margin_right']);
                        $extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top'] + $objattr['margin_bottom']);

                        if ($objattr['itype'] == 'wmf' || $objattr['itype'] == 'svg') {
                            $file = $objattr['file'];
                            $info = $this->formobjects[$file];
                        } else {
                            $file = $objattr['file'];
                            $info = $this->images[$file];
                        }
                        $img_w = $w - $extraWidth;
                        $img_h = $h - $extraHeight;
                        if ($objattr['border_left']['w']) {
                            $objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w'] + $objattr['border_right']['w']) / 2);
                            $objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w'] + $objattr['border_bottom']['w']) / 2);
                            $objattr['BORDER-X'] = $fx + $objattr['margin_left'] + (($objattr['border_left']['w']) / 2);
                            $objattr['BORDER-Y'] = $fy + $objattr['margin_top'] + (($objattr['border_top']['w']) / 2);
                        }
                        $objattr['INNER-WIDTH'] = $img_w;
                        $objattr['INNER-HEIGHT'] = $img_h;
                        $objattr['INNER-X'] = $fx + $objattr['margin_left'] + ($objattr['border_left']['w']);
                        $objattr['INNER-Y'] = $fy + $objattr['margin_top'] + ($objattr['border_top']['w']);
                        $objattr['ID'] = $info['i'];
                        $objattr['OUTER-WIDTH'] = $w;
                        $objattr['OUTER-HEIGHT'] = $h;
                        $objattr['OUTER-X'] = $fx;
                        $objattr['OUTER-Y'] = $fy;
                        if ($objattr['float'] == 'R') {
                            // If R float already exists at this level
                            $this->floatmargins['R']['skipline'] = false;
                            if (isset($this->floatmargins['R']['y1']) && $this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
                                $this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
                            } // If L float already exists at this level
                            elseif (isset($this->floatmargins['L']['y1']) && $this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
                                // Final check distance between floats is not now too narrow to fit text
                                $mw = 2 * $this->GetCharWidth('W', false);
                                if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['L']['w']) < $mw) {
                                    $this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
                                } else {
                                    $this->floatmargins['R']['x'] = $fx;
                                    $this->floatmargins['R']['w'] = $w;
                                    $this->floatmargins['R']['y0'] = $fy;
                                    $this->floatmargins['R']['y1'] = $fy + $h;
                                    if ($skipln == 1) {
                                        $this->floatmargins['R']['skipline'] = true;
                                        $this->floatmargins['R']['id'] = count($this->floatbuffer) + 0;
                                        $objattr['skipline'] = true;
                                    }
                                    $this->floatbuffer[] = $objattr;
                                }
                            } else {
                                $this->floatmargins['R']['x'] = $fx;
                                $this->floatmargins['R']['w'] = $w;
                                $this->floatmargins['R']['y0'] = $fy;
                                $this->floatmargins['R']['y1'] = $fy + $h;
                                if ($skipln == 1) {
                                    $this->floatmargins['R']['skipline'] = true;
                                    $this->floatmargins['R']['id'] = count($this->floatbuffer) + 0;
                                    $objattr['skipline'] = true;
                                }
                                $this->floatbuffer[] = $objattr;
                            }
                        } elseif ($objattr['float'] == 'L') {
                            // If L float already exists at this level
                            $this->floatmargins['L']['skipline'] = false;
                            if (isset($this->floatmargins['L']['y1']) && $this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
                                $this->floatmargins['L']['skipline'] = false;
                                $this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
                            } // If R float already exists at this level
                            elseif (isset($this->floatmargins['R']['y1']) && $this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
                                // Final check distance between floats is not now too narrow to fit text
                                $mw = 2 * $this->GetCharWidth('W', false);
                                if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['R']['w']) < $mw) {
                                    $this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
                                } else {
                                    $this->floatmargins['L']['x'] = $fx + $w;
                                    $this->floatmargins['L']['w'] = $w;
                                    $this->floatmargins['L']['y0'] = $fy;
                                    $this->floatmargins['L']['y1'] = $fy + $h;
                                    if ($skipln == 1) {
                                        $this->floatmargins['L']['skipline'] = true;
                                        $this->floatmargins['L']['id'] = count($this->floatbuffer) + 0;
                                        $objattr['skipline'] = true;
                                    }
                                    $this->floatbuffer[] = $objattr;
                                }
                            } else {
                                $this->floatmargins['L']['x'] = $fx + $w;
                                $this->floatmargins['L']['w'] = $w;
                                $this->floatmargins['L']['y0'] = $fy;
                                $this->floatmargins['L']['y1'] = $fy + $h;
                                if ($skipln == 1) {
                                    $this->floatmargins['L']['skipline'] = true;
                                    $this->floatmargins['L']['id'] = count($this->floatbuffer) + 0;
                                    $objattr['skipline'] = true;
                                }
                                $this->floatbuffer[] = $objattr;
                            }
                        }
                    } else {
                        /* -- END CSS-IMAGE-FLOAT -- */
                        $this->WriteFlowingBlock($vetor[0], (isset($vetor[18]) ? $vetor[18] : null));  // mPDF 5.7.1
                        /* -- CSS-IMAGE-FLOAT -- */
                    }
                    /* -- END CSS-IMAGE-FLOAT -- */
                } // *TABLES*
            } // END If special content
            else { // THE text
                if ($this->tableLevel) {
                    $paint_ht_corr = 0;
                } // To move the y up when new column/page started if div border needed
                else {
                    $paint_ht_corr = $this->blk[$this->blklvl]['border_top']['w'];
                }

                if ($vetor[0] == "\n") { // We are reading a <BR> now turned into newline ("\n")
                    if ($this->flowingBlockAttr['content']) {
                        $this->finishFlowingBlock(false, 'br');
                    } elseif ($is_table) {
                        $this->y += $this->_computeLineheight($this->cellLineHeight);
                    } elseif (!$is_table) {
                        $this->DivLn($this->lineheight);
                        if ($this->ColActive) {
                            $this->breakpoints[$this->CurrCol][] = $this->y;
                        } // *COLUMNS*
                    }
                    // Added to correct for OddEven Margins
                    if ($this->page != $oldpage) {
                        if (($this->page - $oldpage) % 2 == 1) {
                            $bak_x += $this->MarginCorrection;
                        }
                        $oldpage = $this->page;
                        $y = $this->tMargin - $paint_ht_corr;
                        $this->oldy = $this->tMargin - $paint_ht_corr;
                        $old_height = 0;
                    }
                    $this->x = $bak_x;
                    /* -- COLUMNS -- */
                    // COLS
                    // OR COLUMN CHANGE
                    if ($this->CurrCol != $oldcolumn) {
                        if ($this->directionality == 'rtl') { // *OTL*
                            $bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
                        } // *OTL*
                        else { // *OTL*
                            $bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
                        } // *OTL*
                        $this->x = $bak_x;
                        $oldcolumn = $this->CurrCol;
                        $y = $this->y0 - $paint_ht_corr;
                        $this->oldy = $this->y0 - $paint_ht_corr;
                        $old_height = 0;
                    }
                    /* -- END COLUMNS -- */
                    $this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false,
                        $blockdir, $table_draft);
                } else {
                    $this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
                    // Added to correct for OddEven Margins
                    if ($this->page != $oldpage) {
                        if (($this->page - $oldpage) % 2 == 1) {
                            $bak_x += $this->MarginCorrection;
                            $this->x = $bak_x;
                        }
                        $oldpage = $this->page;
                        $y = $this->tMargin - $paint_ht_corr;
                        $this->oldy = $this->tMargin - $paint_ht_corr;
                        $old_height = 0;
                    }
                    /* -- COLUMNS -- */
                    // COLS
                    // OR COLUMN CHANGE
                    if ($this->CurrCol != $oldcolumn) {
                        if ($this->directionality == 'rtl') { // *OTL*
                            $bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
                        } // *OTL*
                        else { // *OTL*
                            $bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
                        } // *OTL*
                        $this->x = $bak_x;
                        $oldcolumn = $this->CurrCol;
                        $y = $this->y0 - $paint_ht_corr;
                        $this->oldy = $this->y0 - $paint_ht_corr;
                        $old_height = 0;
                    }
                    /* -- END COLUMNS -- */
                }
            }

            // Check if it is the last element. If so then finish printing the block
            if ($i == ($array_size - 1)) {
                $this->finishFlowingBlock(true); // true = END of flowing block
                // Added to correct for OddEven Margins
                if ($this->page != $oldpage) {
                    if (($this->page - $oldpage) % 2 == 1) {
                        $bak_x += $this->MarginCorrection;
                        $this->x = $bak_x;
                    }
                    $oldpage = $this->page;
                    $y = $this->tMargin - $paint_ht_corr;
                    $this->oldy = $this->tMargin - $paint_ht_corr;
                    $old_height = 0;
                }

                /* -- COLUMNS -- */
                // COLS
                // OR COLUMN CHANGE
                if ($this->CurrCol != $oldcolumn) {
                    if ($this->directionality == 'rtl') { // *OTL*
                        $bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
                    } // *OTL*
                    else { // *OTL*
                        $bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
                    } // *OTL*
                    $this->x = $bak_x;
                    $oldcolumn = $this->CurrCol;
                    $y = $this->y0 - $paint_ht_corr;
                    $this->oldy = $this->y0 - $paint_ht_corr;
                    $old_height = 0;
                }
                /* -- END COLUMNS -- */
            }

            // RESETTING VALUES
            $this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
            $this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
            $this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
            $this->colorarray = '';
            $this->spanbgcolorarray = '';
            $this->spanbgcolor = false;
            $this->spanborder = false;
            $this->spanborddet = [];
            $this->HREF = '';
            $this->textparam = [];
            $this->SetTextOutline();

            $this->textvar = 0x00; // mPDF 5.7.1
            $this->OTLtags = [];
            $this->textshadow = '';

            $this->currentfontfamily = '';
            $this->currentfontsize = '';
            $this->currentfontstyle = '';
            $this->currentLang = $this->default_lang;  // mPDF 6
            $this->RestrictUnicodeFonts($this->default_available_fonts); // mPDF 6
            /* -- TABLES -- */
            if ($this->tableLevel) {
                $this->SetLineHeight('', $this->table[1][1]['cellLineHeight']); // *TABLES*
            } else {            /* -- END TABLES -- */
                if (isset($this->blk[$this->blklvl]['line_height']) && $this->blk[$this->blklvl]['line_height']) {
                    $this->SetLineHeight('', $this->blk[$this->blklvl]['line_height']); // sets default line height
                }
            }
            $this->ResetStyles();
            $this->lSpacingCSS = '';
            $this->wSpacingCSS = '';
            $this->fixedlSpacing = false;
            $this->minwSpacing = 0;
            $this->SetDash();
            $this->dash_on = false;
            $this->dotted_on = false;
        }//end of for(i=0;i<arraysize;i++)

        $this->Reset(); // mPDF 6
        // PAINT DIV BORDER	// DISABLED IN COLUMNS AS DOESN'T WORK WHEN BROKEN ACROSS COLS??
        if ((isset($this->blk[$this->blklvl]['border']) || isset($this->blk[$this->blklvl]['bgcolor']) || isset($this->blk[$this->blklvl]['box_shadow'])) && $blockstate && ($this->y != $this->oldy)) {
            $bottom_y = $this->y; // Does not include Bottom Margin
            if (isset($this->blk[$this->blklvl]['startpage']) && $this->blk[$this->blklvl]['startpage'] != $this->page && $blockstate != 1) {
                $this->PaintDivBB('pagetop', $blockstate);
            } elseif ($blockstate != 1) {
                $this->PaintDivBB('', $blockstate);
            }
            $this->y = $bottom_y;
            $this->x = $bak_x;
        }

        // Reset Font
        $this->SetFontSize($this->default_font_size, false);
        if ($table_draft) {
            $ch = $this->y - $bak_y;
            $this->y = $bak_y;
            $this->x = $bak_x;
            return $ch;
        }
    }
}
