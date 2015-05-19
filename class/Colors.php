<?php
/**
 * Codigo sacado de:
 * http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
 */

class Colors
{

    private $_foregroundColors = array();
    private $_backgroundColors = array();

    public function __construct()
    {

        // Set up shell colors
        $this->_foregroundColors['black'] = '0;30';
        $this->_foregroundColors['dark_gray'] = '1;30';
        $this->_foregroundColors['blue'] = '0;34';
        $this->_foregroundColors['light_blue'] = '1;34';
        $this->_foregroundColors['green'] = '0;32';
        $this->_foregroundColors['light_green'] = '1;32';
        $this->_foregroundColors['cyan'] = '0;36';
        $this->_foregroundColors['light_cyan'] = '1;36';
        $this->_foregroundColors['red'] = '0;31';
        $this->_foregroundColors['light_red'] = '1;31';
        $this->_foregroundColors['purple'] = '0;35';
        $this->_foregroundColors['light_purple'] = '1;35';
        $this->_foregroundColors['brown'] = '0;33';
        $this->_foregroundColors['yellow'] = '1;33';
        $this->_foregroundColors['light_gray'] = '0;37';
        $this->_foregroundColors['white'] = '1;37';

        $this->_backgroundColors['black'] = '40';
        $this->_backgroundColors['red'] = '41';
        $this->_backgroundColors['green'] = '42';
        $this->_backgroundColors['yellow'] = '43';
        $this->_backgroundColors['blue'] = '44';
        $this->_backgroundColors['magenta'] = '45';
        $this->_backgroundColors['cyan'] = '46';
        $this->_backgroundColors['light_gray'] = '47';
    }

    /**
     * Returns colored string
     */
    public function getColoredString(
        $string,
        $foregroundColor = null,
        $backgroundColors = null
    )
    {

        $coloredString = "";

        /**
         * Check if given foreground color found
         */
        if (isset($this->_foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->_foregroundColors[$foregroundColor] . "m";
        }

        /**
         * Check if given background color found
         */
        if (isset($this->_backgroundColors[$backgroundColors])) {
            $coloredString .= "\033[" . $this->_backgroundColors[$backgroundColors] . "m";
        }

        /**
         * Add string and end coloring
         */
        $coloredString .=  $string . "\033[0m";

        return $coloredString;
    }

    /**
     * Returns all foreground color names
     */
    public function getForegroundColors()
    {
        return array_keys($this->_foregroundColors);
    }

    /**
     * Returns all background color names
     */
    public function getBackgroundColors()
    {
        return array_keys($this->_backgroundColors);
    }
}