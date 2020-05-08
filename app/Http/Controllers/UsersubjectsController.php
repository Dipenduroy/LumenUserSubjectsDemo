<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usersubject;

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
    public function index(Request $request) {
        $this->validate($request, [
            'user_id' => 'required',
        ]);
        $user_id = $request->input('user_id');
        return response()->json(Usersubject::where('user_id', $user_id)->get());
    }

    /**
     * Add a user's subject.
     *
     * @return Response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'user_id' => 'required',
            'user_subject' => 'required'
        ]);
        $user_id = $request->input('user_id');
        $user_subject = $request->input('user_subject', []);
        $data = [];
        foreach ($user_subject as $subject) {
            $data[] = array(
                'user_id' => $user_id, 'subject' => $subject,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }
        Usersubject::insert($data);
        return response()->json(Usersubject::where('user_id', $user_id)->get());
    }

    /**
     * Delete a user's subject.
     *
     * @return Response
     */
    public function destroy(Request $request) {
        $this->validate($request, [
            'user_id' => 'required',
            'user_subject' => 'required',
            '_method' => 'required|in:DELETE'
        ]);
        $user_id = $request->input('user_id');
        $user_subject = $request->input('user_subject');
        Usersubject::where('user_id', $user_id)->where('subject', $user_subject)->delete();
        return response()->json(Usersubject::where('user_id', $user_id)->get());
    }

}
