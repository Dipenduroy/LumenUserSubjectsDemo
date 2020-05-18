<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usersubject;
use Illuminate\Validation\Rule;

class UsersubjectsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Get user subjects.
     *
     * @return Response
     */
    public function index($userid) {
        return response()->json(Usersubject::where('user_id', $userid)->get());
    }

    /**
     * Add a user's subject.
     *
     * @return Response
     */
    public function store(Request $request, $userid) {
        $this->validate($request, [
            'user_subject' => [
                'required',
                'array',
            ],
            'user_subject.*' => [
                Rule::unique('usersubjects', 'subject')->where(function ($query) use ($userid) {
                            $query->where('user_id', $userid);
                        }),
                'distinct'
            ]
        ]);
        $user_subject = $request->input('user_subject', []);
        $data = [];
        foreach ($user_subject as $subject) {
            $data[] = array(
                'user_id' => $userid, 'subject' => $subject,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }
        Usersubject::insert($data);
        return response()->json(Usersubject::where('user_id', $userid)->get(), 201);
    }

    /**
     * Delete a user's subject.
     *
     * @return Response
     */
    public function destroy($userid, $id) {
        $count = Usersubject::where('user_id', $userid)->where('id', $id)->count();
        if ($count == 0) {
            return response()->json([], 404);
        }
        Usersubject::where('user_id', $userid)->where('id', $id)->delete();
        return response()->json(Usersubject::where('user_id', $userid)->get(), 200);
    }

    /**
     * Update a user's subject.
     *
     * @return Response
     */
    public function update(Request $request, $userid, $id) {
        $this->validate($request, [
            'user_subject' => [
                'required',
                'string',
                Rule::unique('usersubjects', 'subject')->where(function ($query) use ($userid) {
                            $query->where('user_id', $userid);
                        })->ignore($id),
            ]
        ]);
        $user_subject = $request->input('user_subject');
        $Usersubject = Usersubject::where('user_id', $userid)->where('id', $id)->first();
        if (empty($Usersubject)) {
            return response()->json([], 404);
        }
        $Usersubject->subject = $user_subject;
        $Usersubject->save();
        return response()->json(Usersubject::where('user_id', $userid)->get(), 200);
    }

}
