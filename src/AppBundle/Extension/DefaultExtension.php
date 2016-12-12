<?php
namespace AppBundle\Extension;

use Pimple\Container;
use Biz\Question\Type\Fill;
use Biz\Question\Type\Essay;
use Biz\Question\Type\Choice;
use Biz\Question\Type\Material;
use Biz\Question\Type\Determine;
use Biz\Question\Type\SingleChoice;
use Pimple\ServiceProviderInterface;
use Biz\Question\Type\UncertainChoice;
use Biz\Testpaper\Pattern\QuestionTypePattern;

class DefaultExtension extends Extension implements ServiceProviderInterface
{
    public function getQuestionTypes()
    {
        return array(
            'single_choice'    => array(
                'name'         => '单选题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/SingleChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/SingleChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/SingleChoiceQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/single-choice-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'choice'           => array(
                'name'         => '多选题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/ChoiceQuestion:create',
                    'edit'   => 'AppBundle:Question/ChoiceQuestion:edit',
                    'show'   => 'AppBundle:Question/ChoiceQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/choice-do.html.twig'
                ),
                'hasMissScore' => 1
            ),
            'essay'            => array(
                'name'         => '问答题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/EssayQuestion:create',
                    'edit'   => 'AppBundle:Question/EssayQuestion:edit',
                    'show'   => 'AppBundle:Question/EssayQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/essay-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'uncertain_choice' => array(
                'name'         => '不定项选择题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/UncertainChoiceQuesiton:create',
                    'edit'   => 'AppBundle:Question/UncertainChoiceQuesiton:edit',
                    'show'   => 'AppBundle:Question/UncertainChoiceQuesiton:show'
                ),
                'templates'    => array(
                    'do' => 'question/uncertain-choice-do.html.twig'
                ),
                'hasMissScore' => 1
            ),
            'determine'        => array(
                'name'         => '判断题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/DetermineQuestion:create',
                    'edit'   => 'AppBundle:Question/DetermineQuestion:edit',
                    'show'   => 'AppBundle:Question/DetermineQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/determine-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'fill'             => array(
                'name'         => '填空题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/FillQuestion:create',
                    'edit'   => 'AppBundle:Question/FillQuestion:edit',
                    'show'   => 'AppBundle:Question/FillQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/fill-do.html.twig'
                ),
                'hasMissScore' => 0
            ),
            'material'         => array(
                'name'         => '材料题',
                'actions'      => array(
                    'create' => 'AppBundle:Question/MaterialQuestion:create',
                    'edit'   => 'AppBundle:Question/MaterialQuestion:edit',
                    'show'   => 'AppBundle:Question/MaterialQuestion:show'
                ),
                'templates'    => array(
                    'do' => 'question/material-do.html.twig'
                ),
                'hasMissScore' => 0
            )
        );
    }

    public function register(Container $container)
    {
        $container['question_type.choice'] = function () {
            return new Choice();
        };
        $container['question_type.single_choice'] = function () {
            return new SingleChoice();
        };
        $container['question_type.uncertain_choice'] = function () {
            return new UncertainChoice();
        };
        $container['question_type.determine'] = function () {
            return new Determine();
        };
        $container['question_type.essay'] = function () {
            return new Essay();
        };
        $container['question_type.fill'] = function () {
            return new Fill();
        };
        $container['question_type.material'] = function () {
            return new Material();
        };

        $container['testpaper_pattern.questionType'] = function ($container) {
            return new QuestionTypePattern($container);
        };

    }
}
