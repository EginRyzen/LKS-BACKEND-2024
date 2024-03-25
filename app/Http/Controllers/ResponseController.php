<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function submit(Request $request, $form_slug)
    {
        $user = Auth::user();
        $form = Form::where('slug', $form_slug)->first();

        // dd($request->answers);
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$form) {
            return response()->json([
                'message' => 'Form Not Found'
            ], 404);
        }

        $countReponse = Response::where('form_id', $form->id)->where('user_id', $user->id)->count();
        // dd($countReponse);
        if ($countReponse >= 1) {
            return response()->json([
                "message" => "You can not submit form twice"
            ]);
        }

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required',
            'answers.*.value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        // dd($validator);

        $response = new Response();
        $response->form_id = $form->id;
        $response->user_id = $user->id;
        $response->date = now();
        $response->save();

        foreach ($request->answers as $answer) {
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'value' => $answer['value']
            ]);
        }

        return response()->json([
            'message' => 'Submit response success'
        ], 200);
    }
    public function getAll($form_slug)
    {
        $user = Auth::user();
        $form = Form::where('slug', $form_slug)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$form) {
            return response()->json([
                'message' => 'Form Not Found'
            ], 404);
        }

        $responses = Response::where('form_id', $form->id)->get(['date', 'id', 'user_id'])->toArray();
        // dd($responses);
        $data = [];
        foreach ($responses as $response) {
            $userData = User::where('id', $response['user_id'])
                ->get(['id', 'name', 'email', 'email_verified_at'])
                ->toArray();

            $answers = Answer::where('response_id', $response['id'])
                ->leftJoin('questions', 'answers.question_id', '=', 'questions.id')
                ->pluck('answers.value', 'questions.name')
                ->toArray();

            $data[] = [
                'date' => $response['date'],
                'user' => $userData[0], // Assuming there is only one user per response
                'answers' => $answers,
            ];
        }

        return response()->json([
            "message" => "Get responses success",
            "responses" => $data
        ]);
    }
}
