<?php namespace MotionArray\Helpers;

use Exception;

class SeoHelper
{
    protected $titleEnd = ' | Motion Array';
    protected $deleteEnds = [' - Motion Array'];
    protected $titleLimit = 70;
    protected $descriptionLimit = 200;

    private static function getInstance()
    {
        return new static;
    }

    private function getFirstValid($options)
    {
        $options = array_map(function ($option) {
            return trim($option);
        }, $options);

        $validOptions = array_values(array_filter($options));

        if (!isset($validOptions[0])) {
            throw new Exception('All values provided for title are empty');
        } else {
            return $validOptions[0];
        }
    }

    /**
     * Crops the content length
     *
     * @param $options
     * @param $limit
     *
     * @return string
     * @throws Exception
     */
    private function getTagContent(Array $options, $limit)
    {
        $content = $this->getFirstValid($options);

        if ($limit) {
            $content = substr($content, 0, $limit);
        }

        return $content;
    }

    public static function getTitle(Array $options)
    {
        $helper = self::getInstance();
        $title = $helper->getTagContent($options, $helper->titleLimit);

        //Delete bad ends
        foreach ($helper->deleteEnds as $badEnd) {
            $re = '#' . preg_quote($badEnd) . '$#i';
            $title = preg_replace($re, '', $title);
        }

        return $title . $helper->titleEnd;
    }

    public static function getDescription(Array $options)
    {
        $helper = self::getInstance();

        try {
            $desc = $helper->getTagContent($options, $helper->descriptionLimit);

            return $desc;
        } catch (Exception $e) {
        }

        return '';
    }
}