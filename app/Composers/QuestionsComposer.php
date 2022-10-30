<?php namespace MotionArray\Composers;

use MotionArray\Repositories\SettingRepository;

class QuestionsComposer
{
    protected $setting;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->setting = $settingRepository;
    }

    public function compose($view)
    {
        $questions = [];

        $questionsObj = $this->setting->getBySlug('siteQuestions');

        if($questionsObj) {
            foreach ($questionsObj->questions as $question) {
                array_push($questions, ['question' => $question->question, 'answer' => $question->answer]);
            }
        }

        $view->with('questions', $questions);
    }

}
