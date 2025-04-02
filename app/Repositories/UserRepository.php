<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\Base64ToFile;
use App\Interfaces\UserInterface;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentParentInfoRepository;
use App\Repositories\StudentAcademicInfoRepository;

class UserRepository implements UserInterface {
    use Base64ToFile;

    protected $parentInfoRepo;
    protected $academicInfoRepo;
    protected $promotionRepo;

    public function __construct(
        StudentParentInfoRepository $parentInfoRepo, 
        StudentAcademicInfoRepository $academicInfoRepo, 
        PromotionRepository $promotionRepo
    ) {
        $this->parentInfoRepo = $parentInfoRepo;
        $this->academicInfoRepo = $academicInfoRepo;
        $this->promotionRepo = $promotionRepo;
    }

    /**
     * @param mixed $request
     * @return string
    */
    public function createTeacher($request) {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'first_name'    => $request['first_name'],
                'last_name'     => $request['last_name'],
                'email'         => $request['email'],
                'gender'        => $request['gender'],
                'nationality'   => $request['nationality'],
                'phone'         => $request['phone'],
                'address'       => $request['address'],
                'address2'      => $request['address2'],
                'city'          => $request['city'],
                'zip'           => $request['zip'],
                'photo'         => !empty($request['photo']) ? $this->convert($request['photo']) : null,
                'role'          => 'teacher',
                'password'      => Hash::make($request['password']),
            ]);

            $permissions = [
                'create exams', 'view exams', 'create exams rule', 'view exams rule',
                'edit exams rule', 'delete exams rule', 'take attendances', 'view attendances',
                'create assignments', 'view assignments', 'save marks', 'view users',
                'view routines', 'view syllabi', 'view events', 'view notices','create users'
            ];
            $user->givePermissionTo($permissions);
        });
    }

    /**
     * @param mixed $request
     * @return string
    */
    public function createStudent($request) {
        DB::transaction(function () use ($request) {
            $student = User::create([
                'first_name'    => $request['first_name'],
                'last_name'     => $request['last_name'],
                'email'         => $request['email'],
                'gender'        => $request['gender'],
                'nationality'   => $request['nationality'],
                'phone'         => $request['phone'],
                'address'       => $request['address'],
                'address2'      => $request['address2'],
                'city'          => $request['city'],
                'zip'           => $request['zip'],
                'photo'         => !empty($request['photo']) ? $this->convert($request['photo']) : null,
                'birthday'      => $request['birthday'],
                'religion'      => $request['religion'],
                'blood_type'    => $request['blood_type'],
                'role'          => 'student',
                'password'      => Hash::make($request['password']),
            ]);

            $this->parentInfoRepo->store($request, $student->id);
            $this->academicInfoRepo->store($request, $student->id);
            $this->promotionRepo->assignClassSection($request, $student->id);

            $student->givePermissionTo([
                'view attendances', 'view assignments', 'submit assignments', 'view exams',
                'view marks', 'view users', 'view routines', 'view syllabi', 'view events', 'view notices'
            ]);
        });
    }

    public function updateStudent($request) {
        DB::transaction(function () use ($request) {
            User::where('id', $request['student_id'])->update(array_filter([
                'first_name' => $request['first_name'],
                'last_name'  => $request['last_name'],
                'email'      => $request['email'],
                'gender'     => $request['gender'],
                'nationality'=> $request['nationality'],
                'phone'      => $request['phone'],
                'address'    => $request['address'],
                'address2'   => $request['address2'],
                'city'       => $request['city'],
                'zip'        => $request['zip'],
                'birthday'   => $request['birthday'],
                'religion'   => $request['religion'],
                'blood_type' => $request['blood_type'],
            ]));

            $this->parentInfoRepo->update($request, $request['student_id']);
            $this->promotionRepo->update($request, $request['student_id']);
        });
    }

    public function updateTeacher($request) {
        User::where('id', $request['teacher_id'])->update(array_filter([
            'first_name' => $request['first_name'],
            'last_name'  => $request['last_name'],
            'email'      => $request['email'],
            'gender'     => $request['gender'],
            'nationality'=> $request['nationality'],
            'phone'      => $request['phone'],
            'address'    => $request['address'],
            'address2'   => $request['address2'],
            'city'       => $request['city'],
            'zip'        => $request['zip'],
        ]));
    }

    public function getAllStudents($session_id, $class_id, $section_id) {
        if (!$class_id || !$section_id) {
            $schoolClass = SchoolClass::where('session_id', $session_id)->first();
            $section = Section::where('session_id', $session_id)->first();
            if (!$schoolClass || !$section) {
                throw new \Exception('No class and section found');
            }
            $class_id = $schoolClass->id;
            $section_id = $section->id;
        }
        return $this->promotionRepo->getAll($session_id, $class_id, $section_id);
    }

    public function getAllStudentsBySession($session_id) {
        return $this->promotionRepo->getAllStudentsBySession($session_id);
    }

    public function getAllStudentsBySessionCount($session_id) {
        $promotionRepository = new PromotionRepository();
        return $promotionRepository->getAllStudentsBySessionCount($session_id);
    }

    public function findStudent($id) {
        return User::with(['parent_info', 'academic_info'])->findOrFail($id);
    }

    public function findTeacher($id) {
        return User::where('id', $id)->where('role', 'teacher')->firstOrFail();
    }

    public function getAllTeachers() {
        return User::where('role', 'teacher')->get();
    }

    public function changePassword($new_password) {
        return auth()->user()->update(['password' => Hash::make($new_password)]);
    }
}