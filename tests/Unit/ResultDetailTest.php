<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\ResultDetail;
use Tests\TestCase;

class ResultDetailTest extends TestCase
{
    public function test_is_correct_returns_true_when_selected_option_matches_right_option(): void
    {
        $question = new Question([
            'right_option' => '2',
        ]);

        $detail = new ResultDetail([
            'selected_option' => 2,
        ]);
        $detail->setRelation('question', $question);

        $this->assertTrue($detail->is_correct);
    }

    public function test_is_correct_returns_false_when_selected_option_is_wrong(): void
    {
        $question = new Question([
            'right_option' => '3',
        ]);

        $detail = new ResultDetail([
            'selected_option' => 1,
        ]);
        $detail->setRelation('question', $question);

        $this->assertFalse($detail->is_correct);
    }

    public function test_is_correct_returns_false_when_option_is_null_or_unanswered(): void
    {
        $question = new Question([
            'right_option' => '1',
        ]);

        $detail = new ResultDetail([
            'selected_option' => null,
        ]);
        $detail->setRelation('question', $question);

        $this->assertFalse($detail->is_correct);
    }

    public function test_is_correct_returns_false_when_question_relation_is_missing(): void
    {
        $detail = new ResultDetail([
            'selected_option' => 1,
        ]);

        $this->assertFalse($detail->is_correct);
    }
}
