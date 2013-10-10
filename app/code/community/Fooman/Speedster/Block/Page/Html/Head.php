<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com) (original implementation)
 * @copyright  Copyright (c) 2008 Fooman (http://www.fooman.co.nz) (use of Minify Library)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Speedster Html Head Block
 *
 * @package Fooman_Speedster
 * @author  Kristof Ringleff <kristof@fooman.co.nz>
 */

class Fooman_Speedster_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    protected $_bundleItems = array(
        'js' => array(),
        'skin_css' => array(),
        'skin_js' => array(),
    );

    public function setBundleItems($type, $files)
    {
        $files = (array)$files;
        $this->_bundleItems[$type] = $files;
    }

    public function getCssJsHtml()
    {
        if (!Mage::getStoreConfigFlag('foomanspeedster/settings/enabled')) {
            return parent::getCssJsHtml();
        }
        $webroot="/";

        $lines = array();

        $baseJs = Mage::getBaseUrl('js');
        $baseJsFast = Mage::getBaseUrl('skin') . 'm/';
        $html = '';
        //$html = "<!--".BP."-->\n";
        $script = '<script type="text/javascript" src="%s" %s></script>';
        $stylesheet = '<link type="text/css" rel="stylesheet" href="%s" %s />';
        $alternate = '<link rel="alternate" type="%s" href="%s" %s />';

        $bundleItems = $this->_bundleItems;
        $bundleFiles = array();
        foreach ($bundleItems as $type => $files) {
            foreach ($files as $file) {
                $this->addItem($type, $file);
            }
        }

        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond'])) {
                continue;
            }
            $if = !empty($item['if']) ? $item['if'] : '';
            switch ($item['type']) {
                case 'js':
                    if (strpos($item['name'], 'packaging.js') !== false) {
                        $item['name'] = $baseJs . $item['name'];
                        $lines[$if]['script_direct'][] = $item;
                    } else {
                        $lines[$if]['script']['global'][] = "/" . $webroot . "js/" . $item['name'];
                        if (in_array($item['name'], $bundleItems['js'])) {
                            $bundleFiles[] = $filename;
                        }
                    }
                    break;

                case 'script_direct':
                    $lines[$if]['script_direct'][] = $item;
                    break;

                case 'css_direct':
                    $lines[$if]['css_direct'][] = $item;
                    break;

                case 'js_css':
                    $lines[$if]['other'][] = sprintf($stylesheet, $baseJs . $item['name'], $item['params']);
                    break;

                case 'skin_js':
                    $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                    $lines[$if]['script']['skin'][] = "/" . $webroot . "skin" . $chunks[1];
                    if (in_array($item['name'], $bundleItems['skin_js'])) {
                        $bundleFiles[] = $filename;
                    }
                    break;

                case 'skin_css':
                    if ($item['params'] == 'media="all"') {
                        $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                        $filename = $lines[$if]['stylesheet'][] = "/" . $webroot . "skin" . $chunks[1];
                        if (in_array($item['name'], $bundleItems['skin_css'])) {
                            $bundleFiles[] = $filename;
                        }
                    } elseif($item['params']=='media="print"'){
                        $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                        $filename = $lines[$if]['stylesheet_print'][] = "/" . $webroot . "skin" . $chunks[1];
                        if (in_array($item['name'], $bundleItems['skin_css'])) {
                            $bundleFiles[] = $filename;
                        }
                    } else {
                        $lines[$if]['other'][] = sprintf(
                            $stylesheet, $this->getSkinUrl($item['name']), $item['params']
                        );
                    }
                    break;

                case 'rss':
                    $lines[$if]['other'][] = sprintf(
                        $alternate, 'application/rss+xml' /*'text/xml' for IE?*/, $item['name'], $item['params']
                    );
                    break;

                case 'link_rel':
                    $lines[$if]['other'][] = sprintf('<link %s href="%s" />', $item['params'], $item['name']);
                    break;

                case 'ext_js':
                default:
                    $lines[$if]['other'][] = sprintf(
                        '<script type="text/javascript" src="%s"></script>', $item['name']
                    );
                    break;

            }
        }

        foreach ($lines as $if => $items) {
            if (!empty($if)) {
                $html .= '<!--[if ' . $if . ']>' . "\n";
            }
            if (!empty($items['stylesheet'])) {
                $cssBuild = Mage::getModel('speedster/buildSpeedster', array($items['stylesheet'], BP));
                foreach ($this->_getChunkedItems($items['stylesheet'], $baseJsFast.$cssBuild->getLastModified(), null, $bundleFiles) as $item) {
                    $html .= sprintf($stylesheet, $item, 'media="all"') . "\n";
                }
            }
            if (!empty($items['script']['global']) || !empty($items['script']['skin'])) {
                if (!empty($items['script']['global']) && !empty($items['script']['skin'])) {
                    $mergedScriptItems = array_merge($items['script']['global'], $items['script']['skin']);
                } elseif (!empty($items['script']['global']) && empty($items['script']['skin'])) {
                    $mergedScriptItems = $items['script']['global'];
                } else {
                    $mergedScriptItems = $items['script']['skin'];
                }
                $jsBuild = Mage::getModel('speedster/buildSpeedster', array($mergedScriptItems, BP));
                $chunkedItems = $this->_getChunkedItems($mergedScriptItems, $baseJsFast . $jsBuild->getLastModified(), null, $bundleFiles);
                foreach ($chunkedItems as $item) {
                    $html .= sprintf($script, $item, '') . "\n";
                }
            }
            if (!empty($items['css_direct'])) {
                foreach ($items['css_direct'] as $item) {
                    $html .= sprintf($stylesheet, $item['name']) . "\n";
                }
            }
            if (!empty($items['script_direct'])) {
                foreach ($items['script_direct'] as $item) {
                    $html .= sprintf($script, $item['name'], '') . "\n";
                }
            }
            if (!empty($items['stylesheet_print'])) {
                $cssBuild = Mage::getModel('speedster/buildSpeedster', array($items['stylesheet_print'], BP));
                foreach ($this->_getChunkedItems($items['stylesheet_print'], $baseJsFast . $cssBuild->getLastModified(), null, $bundleFiles) as $item) {
                    $html .= sprintf($stylesheet, $item, 'media="print"') . "\n";
                }
            }
            if (!empty($items['other'])) {
                $html .= join("\n", $items['other']) . "\n";
            }
            if (!empty($if)) {
                $html .= '<![endif]-->' . "\n";
            }
        }
        return $html;
    }

    protected function _getChunkedItems($files, $prefix='', $maxLen=null, $bundleFiles)
    {
        if (!Mage::getStoreConfigFlag('foomanspeedster/settings/enabled')) {
            return parent::getChunkedItems($items, $prefix, 450);
        }
        if ($maxLen === null) {
            // URLs of up to 2000 characters are no problem for any client/server combination
            $maxLen = 2000;
        }
        $chunks = array();

        $addBundleFiles = array();
        foreach ($files as $i => $file) {
            if (in_array($file, $bundleFiles)) {
                $addBundleFiles[] = $file;
                unset($files[$i]);
            }
        }

        foreach (array($addBundleFiles, $files) as $items) {
            $chunk = '';
            foreach ($items as $i => $item) {
                if (strlen($prefix) + strlen($chunk) + strlen($item) > $maxLen) {
                    $chunks[] = $prefix . $chunk;
                    $chunk = '';
                }
                $chunk .= (($chunk) ? ',' : '') . substr($item, 1);
            }
            if ($chunk) {
                $chunks[] = $prefix . $chunk;
            }
        }

        return $chunks;
    }


}