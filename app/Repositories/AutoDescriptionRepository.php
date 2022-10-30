<?php

namespace MotionArray\Repositories;

use MotionArray\Models\AutoDescription;
use MotionArray\Models\Product;

class AutoDescriptionRepository
{
    /**
     * @param Product $video
     * @param string $overrideName
     * @return string
     */
    public function generateStockVideoDescription(Product $video, string $overrideName = null): string
    {
        $sentences = ucfirst($this->generateStockVideoSentence('sentence-1', $video, $overrideName)) . '. ' .
            ucfirst($this->generateStockVideoSentence('sentence-2', $video, $overrideName)) . '. ' .
            ucfirst($this->generateStockVideoSentence('sentence-3', $video, $overrideName)) . '. ' .
            ucfirst($this->generateStockVideoSentence('sentence-4', $video, $overrideName)) . '.';

        $sentences = $this->fixSentences($sentences);

        return $sentences;
    }

    /**
     * @param string $sentenceName
     * @param Product $video
     * @param string $overrideName
     * @return mixed
     */
    private function generateStockVideoSentence(string $sentenceName, Product $video, string $overrideName = null): string
    {
        $autoDescription = AutoDescription::whereCategory('stock-video-description')->whereName($sentenceName)->first();
        $sentenceStructures = $autoDescription->data['sentence_structures'];
        $sentence = $sentenceStructures[array_rand($sentenceStructures)]; // Pick one random sentence
        $variables = $autoDescription->data['variables'];

        // Get all words in the sentence that are enclosed in braces {} and put in $placeholders
        preg_match_all('/{([^}]*)}/', $sentence, $placeholders);
        $placeholders = array_unique($placeholders[1]);
        foreach ($placeholders as $placeholder) {
            if ($placeholder === 'name') {
                if ($overrideName !== null) {
                    $name = ucwords($overrideName);
                } else {
                    $name = ucwords($video->name);
                }
                $sentence = str_replace('{' . $placeholder . '}', $name, $sentence);
            } elseif ($placeholder === 'resolution') {
                $resolution = $video->resolutions()->first();
                $sentence = str_replace('{' . $placeholder . '}', $resolution->name, $sentence);
            } else {
                $placeholderOccurrences = substr_count($sentence, $placeholder);
                $selectedVariableIndexes = array_rand($variables[$placeholder], $placeholderOccurrences);
                foreach((array)$selectedVariableIndexes as $selectedVariableIndex) {
                    $$placeholder = $variables[$placeholder][$selectedVariableIndex];
                    $sentence = str_replace_first('{' . $placeholder . '}', $$placeholder, $sentence);
                }
            }
        }

        return $sentence;
    }

    /**
     * Performs various cleanup functions on the sentences.
     *
     * @param string $inputString
     * @return string
     */
    private function fixSentences (string $inputString) {
        $sentence = $inputString;
        $sentence = $this->aToAn($sentence);
        $sentence = $this->removeDoublePeriods($sentence);

        return $sentence;
    }

    /**
     * Replaces 'a' with 'an' when next word starts with a vowel.
     *
     * @param string $inputString
     * @return string
     */
    private function aToAn (string $inputString) {
        return preg_replace('/\b(a)\s+([aeiou])/i', '$1n $2', $inputString);
    }

    /**
     * Replaces 'a' with 'an' when next word starts with a vowel.
     *
     * @param string $inputString
     * @return string
     */
    private function removeDoublePeriods (string $inputString) {
        return str_replace('..', '.', $inputString);
    }
}
