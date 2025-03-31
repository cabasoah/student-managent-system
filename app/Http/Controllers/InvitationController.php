<?php

namespace App\Http\Controllers;

use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\AssignedTeacher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class InvitationController extends Controller
{
    use SchoolSession;
    protected $schoolClassRepository;
    protected $schoolSessionRepository;

    public function __construct(SchoolClassInterface $schoolClassRepository, SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }
    public function create()
    {
        $courses = AssignedTeacher::with('course')->where('teacher_id', auth()->id())->get();
        $courses = $courses->pluck('course');
        
        return view('invitations.create', compact('courses'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'email' => 'nullable|email',
            'expiry_days' => 'required|integer|min:1|max:30'
        ]);

        $invitation = Invitation::create([
            'token' => Str::random(32),
            'lecturer_id' => auth()->id(),
            'course_id' => $request->course_id,
            'email' => $request->email,
            'expires_at' => Carbon::today()->addDays($request->expiry_days),
        ]);

        $url = URL::signedRoute('register.invited', ['token' => $invitation->token]);

        return back()->with('success', 'Invitation generated!')
                    ->with('invitation_url', $url);
    }

    public function index()
    {
        $invitations = Invitation::where('lecturer_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('invitations.index', compact('invitations'));
    }

    public function createFromInvite($token)
    {
        $invitation = Invitation::where('token', $token)->where('used', false)->where('expires_at', '>', now())->firstOrFail();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
       
        return view('students.register', compact('invitation', 'school_classes', 'current_school_session_id'));
    }
}
