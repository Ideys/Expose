<?php

namespace Ideys\Messaging;

/**
 * Helper to manage spam checker.
 */
class SpicedHamHelper
{
    private $questions = array(
        ['q' => '2 + 5',  'a' => '7'],
        ['q' => '6 + 10', 'a' => '16'],
        ['q' => '11 + 4', 'a' => '15'],
        ['q' => '17 + 3', 'a' => '20'],
        ['q' => '19 + 3', 'a' => '22'],
        ['q' => '8 + 22', 'a' => '30'],
    );

    /**
     * @return array
     */
    public function getRandomQuestion()
    {
        shuffle($this->questions);

        return array_pop($this->questions);
    }

    /**
     * Check if question is well answered.
     *
     * @param $question
     * @param $answer
     *
     * @return bool
     */
    public function isAnswerRight($question, $answer)
    {
        foreach ($this->questions as $q_a) {
            if ($q_a['q'] == $question) {
                return (int) $q_a['a'] == (int) $answer;
            }
        }

        return false;
    }
}
