<?php

namespace App\Http\Controllers;

use App\Event;
use App\Feedback;
use App\Mail\SendMailFeedback;
use App\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Add feedback
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => ['required', 'min:10'],
        ]);
        $feedback = Feedback::create($request->only(['content', 'user_id']));
        //Todo: send email
        if ($feedback) {
            \Mail::to(config('constant.mail_contact'))->queue(new SendMailFeedback($feedback));
        }

        return response()->json(['message' => 'ok']);
    }

    public function report($id, Request $request)
    {
        $project = Project::with(['tasks', 'teams'])->findOrFail($id);
        $pdf = Pdf::loadView('report', ['project' => $project])->setOptions(['defaultFont' => 'sans-serif']);

        $name_file = 'report'.'-'.now().'.pdf';

        return $pdf->download($name_file);
    }

    public function reportProjectTask($id, Request $request)
    {
        $projectTask = Event::projectTask()->findOrFail($id);
        $project = Project::with(['tasks', 'teams'])->findOrFail($projectTask->project_id);
        $pdf = Pdf::loadView('report', ['project' => $project])->setOptions(['defaultFont' => 'sans-serif']);

        $name_file = 'report'.'-'.now().'.pdf';

        return $pdf->download($name_file);
    }
}
