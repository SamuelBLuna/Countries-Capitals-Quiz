<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class MainController extends Controller
{
    private $app_data;

    public function __construct()
    {
        // load app_data file from app folder
        $this->app_data = require(\app_path('app_data.php'));
    }

    public function startGame(): View
    {
        return view('home');
    }

    public function prepareGame(Request $request)
    {
        // validate request
        $request->validate(
            [
                'total_questions' => 'required|min:3|max:30|integer'
            ],
            [
                'total_questions.required' => 'O número de questões é obrigatório',
                'total_questions.integer' => 'O número de questões tem que ser um valor inteiro',
                'total_questions.min' => 'No mínimo :min questões',
                'total_questions.max' => 'no máximo :max questões'
            ]
        );

        $total_questions = \intval($request->input('total_questions'));

        $quiz = $this->prepareQuiz($total_questions);

        \session()->put([
            'quiz' => $quiz,
            'total_questions' => $total_questions,
            'current_question' => 1,
            'current_answers' => 0,
            'wrong_answers' => 0,
        ]);

        return \redirect()->route('game');
    }

    private function prepareQuiz($total_questions)
    {
        $questions = [];
        $total_countries = count($this->app_data);

        $indexes = range(0, $total_countries - 1);
        shuffle($indexes);
        $indexes = array_slice($indexes, 0, $total_questions); // Corrigido para pegar a quantidade correta de perguntas

        $question_number = 1;
        foreach ($indexes as $index) {
            $question = []; // Iniciar um novo array para cada pergunta
            $question['question_number'] = $question_number++;
            $question['country'] = $this->app_data[$index]['country'];
            $question['correct_answer'] = $this->app_data[$index]['capital']; // Corrigido "currect_answer" e "capiral"

            $other_capitals = array_column($this->app_data, 'capital');

            // Remover a resposta correta da lista de outras capitais
            $other_capitals = array_diff($other_capitals, [$question['correct_answer']]);

            shuffle($other_capitals);
            $question['wrong_answers'] = array_slice($other_capitals, 0, 3); // Corrigido "wrong_anwers"

            $question['correct'] = null; // Se necessário, defina o que deve ir aqui

            $questions[] = $question; // Armazenar corretamente cada pergunta no array de perguntas
        }

        return $questions;
    }

    public function game(): View
    {
        $quiz = \session('quiz');
        $total_questions = \session('total_questions');
        $current_question = \session('current_question') - 1;

        $answers = $quiz[$current_question]['wrong_answers'];
        $answers[] = $quiz[$current_question]['correct_answer'];

        \shuffle($answers);


        return view('game')->with([
            'country' =>  $quiz[$current_question]['country'],
            'totalQuestions' => $total_questions,
            'currentQuestion' => $current_question,
            'answers' => $answers
        ]);
    }

    public function answer($enc_answer)
    {
        try {
            $answer = Crypt::decryptString($enc_answer);
        } catch (Exception $e) {
            return \redirect()->route('game');
        }

        // game logic
        $quiz = \session('quiz');
        $current_question = \session('current_question') - 1;
        $correct_answer = $quiz[$current_question]['correct_answer'];
        $correct_answers = \session('correct_answers');
        $wrong_answres = \session('wrong_answres');

        if ($answer == $correct_answer) {
            $correct_answers++;
            $quiz[$current_question]['correct'] = \true;
        } else {
            $wrong_answres++;
            $quiz[$current_question]['correct'] = \false;
        }

        \session()->put([
            'quiz' => $quiz,
            'correct_answers' => $correct_answers,
            'wrong_answres' => $wrong_answres,
        ]);

        $data = [
            'country' => $quiz[$current_question]['country'],
            'correct_answer' => $correct_answer,
            'choice_answer' => $answer,
            'currentQuestion' => $current_question,
            "totalQuestions" => \session('total_questions')
        ];

        return view('answer_result')->with($data);
    }

    public function nextQuestion()
    {
        $current_question = \session('current_question');
        $total_questions = \session('total_questions');

        if ($current_question < $total_questions) {
            $current_question++;
            \session()->put('current_question', $current_question);
            return \redirect()->route('game');
        } else {
            return \redirect()->route('show_result');
        }
    }

    public function showResult()
    {
        echo 'Final do jogo';
    }
}
