<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for transformation wrappers
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
use PMA\libraries\Theme;

require_once 'libraries/transformations.lib.php';

require_once 'libraries/database_interface.inc.php';

require_once 'libraries/relation.lib.php';


/**
 * tests for transformation wrappers
 *
 * @package PhpMyAdmin-test
 */
class PMA_Transformation_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Set up global environment.
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['table'] = 'table';
        $GLOBALS['db'] = 'db';
        $_SESSION['PMA_Theme'] = Theme::load('./themes/pmahomme');
        $GLOBALS['cfg'] = array(
            'ServerDefault' => 1,
            'ActionLinksMode' => 'icons',
        );
        $GLOBALS['server'] = 1;
        $GLOBALS['cfg']['Server']['pmadb'] = 'pmadb';
        $GLOBALS['cfg']['Server']['user'] = 'user';
        $GLOBALS['cfg']['Server']['bookmarktable'] = '';
        $GLOBALS['cfg']['Server']['relation'] = '';
        $GLOBALS['cfg']['Server']['table_info'] = '';
        $GLOBALS['cfg']['Server']['table_coords'] = '';
        $GLOBALS['cfg']['Server']['column_info'] = 'column_info';
        $GLOBALS['cfg']['DBG']['sql'] = false;
        // need to clear relation test cache
        unset($_SESSION['relation']);
    }

    /**
     * Test for parsing options.
     *
     * @param string $input    String to parse
     * @param array  $expected Expected result
     *
     * @return void
     *
     * @dataProvider getOptionsData
     */
    public function testGetOptions($input, $expected)
    {
        $this->assertEquals(
            $expected,
            PMA_Transformation_getOptions($input)
        );
    }

    /**
     * Data provided for parsing options
     *
     * @return array with test data
     */
    public function getOptionsData()
    {
        return array(
            array("option1 , option2 ", array('option1 ', ' option2 ')),
            array("'option1' ,' option2' ", array('option1', ' option2')),
            array("'2,3' ,' ,, option ,,' ", array('2,3', ' ,, option ,,')),
            array("'',,", array('', '', '')),
            array('', array()),
        );
    }

    /**
     * Test for getting available types.
     *
     * @return void
     */
    public function testGetTypes()
    {
        $this->assertEquals(
            array (
                'mimetype' => array (
                    'Application/Octetstream' => 'Application/Octetstream',
                    'Image/JPEG' => 'Image/JPEG',
                    'Image/PNG' => 'Image/PNG',
                    'Text/Plain' => 'Text/Plain',
                    'Text/Octetstream' => 'Text/Octetstream'
                ),
                'transformation' => array (
                    0 => 'Application/Octetstream: Download',
                    1 => 'Application/Octetstream: Hex',
                    2 => 'Image/JPEG: Inline',
                    3 => 'Image/JPEG: Link',
                    4 => 'Image/PNG: Inline',
                    5 => 'Text/Octetstream: Sql',
                    6 => 'Text/Plain: Binarytoip',
                    7 => 'Text/Plain: Bool2text',
                    8 => 'Text/Plain: Dateformat',
                    9 => 'Text/Plain: External',
                    10 => 'Text/Plain: Formatted',
                    11 => 'Text/Plain: Imagelink',
                    12 => 'Text/Plain: Json',
                    13 => 'Text/Plain: Sql',
                    14 => 'Text/Plain: Xml',
                    15 => 'Text/Plain: Link',
                    16 => 'Text/Plain: Longtoipv4',
                    17 => 'Text/Plain: Preappend',
                    18 => 'Text/Plain: Substring',
                    ),
                'transformation_file' => array (
                    0 => 'output/ApplicationOctetstreamDownload.php',
                    1 => 'output/ApplicationOctetstreamHex.php',
                    2 => 'output/ImageJPEGInline.php',
                    3 => 'output/ImageJPEGLink.php',
                    4 => 'output/ImagePNGInline.php',
                    5 => 'output/TextOctetstreamSql.php',
                    6 => 'output/TextPlainBinarytoip.php',
                    7 => 'output/TextPlainBool2Text.php',
                    8 => 'output/TextPlainDateformat.php',
                    9 => 'output/TextPlainExternal.php',
                    10 => 'output/TextPlainFormatted.php',
                    11 => 'output/TextPlainImagelink.php',
                    12 => 'output/TextPlainJson.php',
                    13 => 'output/TextPlainSql.php',
                    14 => 'output/TextPlainXml.php',
                    15 => 'TextPlainLink.php',
                    16 => 'TextPlainLongtoipv4.php',
                    17 => 'TextPlainPreApPend.php',
                    18 => 'TextPlainSubstring.php',
                ),
                'input_transformation' => array(
                    'Image/JPEG: Upload',
                    'Text/Plain: Fileupload',
                    'Text/Plain: Iptobinary',
                    'Text/Plain: JsonEditor',
                    'Text/Plain: Regexvalidation',
                    'Text/Plain: SqlEditor',
                    'Text/Plain: XmlEditor',
                    'Text/Plain: Link',
                    'Text/Plain: Longtoipv4',
                    'Text/Plain: Preappend',
                    'Text/Plain: Substring',
                ),
                'input_transformation_file' => array(
                    'input/ImageJPEGUpload.php',
                    'input/TextPlainFileupload.php',
                    'input/TextPlainIptobinary.php',
                    'input/TextPlainJsonEditor.php',
                    'input/TextPlainRegexvalidation.php',
                    'input/TextPlainSqlEditor.php',
                    'input/TextPlainXmlEditor.php',
                    'TextPlainLink.php',
                    'TextPlainLongtoipv4.php',
                    'TextPlainPreApPend.php',
                    'TextPlainSubstring.php',
                ),
            ),
            PMA_getAvailableMIMEtypes()
        );
    }

    /**
     * Tests getting mime types for table
     *
     * @return void
     */
    public function testGetMime()
    {
        $_SESSION['relation'][$GLOBALS['server']]['PMA_VERSION'] = PMA_VERSION;
        $_SESSION['relation'][$GLOBALS['server']]['commwork'] = true;
        $_SESSION['relation'][$GLOBALS['server']]['db'] = "pmadb";
        $_SESSION['relation'][$GLOBALS['server']]['column_info'] = "column_info";
        $_SESSION['relation'][$GLOBALS['server']]['trackingwork'] = false;
        $this->assertEquals(
            array(
                'o' => array(
                    'column_name' => 'o',
                    'mimetype' => 'Text/plain',
                    'transformation' => 'Sql',
                    'transformation_options' => '',
                    'input_transformation' => 'regex',
                    'input_transformation_options' => '/pma/i',
                ),
                'col' => array(
                    'column_name' => 'col',
                    'mimetype' => 'T',
                    'transformation' => 'o/P',
                    'transformation_options' => '',
                    'input_transformation' => 'i/p',
                    'input_transformation_options' => '',
                ),
            ),
            PMA_getMIME('pma_test', 'table1')
        );
    }

    /**
     * Test for PMA_Transformation_globalHtmlReplace
     *
     * @return void
     */
    public function testTransformationGlobalHtmlReplace()
    {
        // Case 1
        $actual = PMA_Transformation_globalHtmlReplace('', array());
        $this->assertEquals(
            '',
            $actual
        );

        // Case 2
        $buffer = 'foobar';
        $options = array(
            'regex' => 'foo',
            'regex_replace' => 'bar',
            'string' => 'x[__BUFFER__]x'
        );
        $actual = PMA_Transformation_globalHtmlReplace($buffer, $options);
        $this->assertEquals(
            'xbarbarx',
            $actual
        );
    }

    /**
     * Test for PMA_clearTransformations
     *
     * @return void
     */
    public function testClearTransformations()
    {
        // Mock dbi
        $dbi = $this->getMockBuilder('PMA\libraries\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dbi->expects($this->any())
            ->method('tryQuery')
            ->will($this->returnValue(true));
        $GLOBALS['dbi'] = $dbi;

        // Case 1 : no configuration storage
        $actual = PMA_clearTransformations('db');
        $this->assertEquals(
            false,
            $actual
        );

        $_SESSION['relation'][$GLOBALS['server']]['PMA_VERSION'] = PMA_VERSION;
        $_SESSION['relation'][$GLOBALS['server']]['column_info'] = "column_info";
        $_SESSION['relation'][$GLOBALS['server']]['db'] = "pmadb";

        // Case 2 : database delete
        $actual = PMA_clearTransformations('db');
        $this->assertEquals(
            true,
            $actual
        );

        // Case 3 : table delete
        $actual = PMA_clearTransformations('db', 'table');
        $this->assertEquals(
            true,
            $actual
        );

        // Case 4 : column delete
        $actual = PMA_clearTransformations('db', 'table', 'col');
        $this->assertEquals(
            true,
            $actual
        );
    }
}
