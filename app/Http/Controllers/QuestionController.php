<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Response;
use App\Models\Question;
use App\Models\Form;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function addQuest(Request $request, $form_slug)
    {
        $validator = Validator::make($request->all() ,[
            'name' => 'required',
            'choice_type' => 'required',
            'choices' => 'nullable',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $form = Form::where('slug', $form_slug)->firstOrFail();

        if(!$form){
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        $question = new Question();
        $question->name = $request->name;
        $question->choice_type = $request->choice_type;
        $question->choices = $request->choices;
        $question->is_required = $request->is_required == 1 ? true : false;
        $question->form_id = $form->id;
        $question->save();

        return response()->json([
            'message' => 'Add question success',
            'question' => $question
        ], 200);
    }
    public function removeQuest(Request $request, $form_slug, $question_id)
    {
    $user = Auth::user();
    $form = Form::where('slug', $form_slug)->firstOrFail();
            
    if ($form->creator_id !== $user->id) {
        return response()->json([
            'message' => 'Forbidden access'
        ], 403);
    }

    $question = Question::where('id', $question_id)->firstOrFail();
            
    if ($question->form_id !== $form->id) {
        return response()->json([
            'message' => 'Question not found'
        ], 404);
    }

    $question->delete();

    return response()->json([
        'message' => 'Remove question success'
    ], 200);
        if (isset($form) && !empty($form)) {
            return response()->json([
                'message' => 'Question not found'], 404);
        } else {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }
    }
}
