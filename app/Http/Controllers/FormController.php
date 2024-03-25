<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Question;
use App\Models\AllowedDomain;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:forms|regex:/^[a-z0-9\-\.]+$/i',
            'description' => 'nullable|string',
            'domain' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $form = new Form();
        $form->name = $request->name;
        $form->slug = $request->slug;
        $form->description = $request->description;
        $form->limit_one_response = 1;
        $form->creator_id = Auth::id();
        $form->save();

        $domain = new AllowedDomain();
        $domain->domain = $request->domain;
        $domain->form_id = $form->id;
        $domain->save();

        $data = AllowedDomain::join('forms', 'allowed_domains.form_id', '=', 'forms.id')
            ->where('allowed_domains.id', $domain->id)
            ->select('forms.name', 'forms.slug', 'forms.description', 'forms.limit_one_response', 'allowed_domains.domain as allowed_domains', 'forms.creator_id', 'forms.id')
            ->first();

        if ($data) {
            $data->limit_one_response = $data->limit_one_response == 1 ? true : false;
            return response()->json([
                'message' => 'Create form success',
                'form' => $data,
            ], 200);
        }
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
    public function getAll(Request $request)
    {
        if ($user = Auth::user()) {
            $user = Auth::user();
            $forms = Form::where('creator_id', $user->id)->get();

            return response()->json([
                'message' => 'Get all forms success',
                'forms' => $forms,
            ], 200);
        }
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
    public function getDetail($form_slug)
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

        $allowed_domains = AllowedDomain::where('form_id', $form->id)
            ->pluck('domain')
            ->toArray();

        $questions = Question::where('form_id', $form->id)
            ->get(['id', 'name', 'choice_type', 'choices', 'is_required'])
            ->toArray();

        if ($allowed_domains) {
            $data = [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'description' => $form->description,
                'limit_one_response' => $form->limit_one_response == 1,
                'creator_id' => $form->creator_id,
                'allowed_domains' => $allowed_domains,
                'questions' => $questions,
            ];
            return response()->json([
                'message' => 'Get form success',
                'forms' => $data
            ], 200);
        }
    }
}
