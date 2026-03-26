<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomGrade;
use App\Models\User;
use App\Services\Catalog\ClassroomGradeNotificationService;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private ClassroomGradeNotificationService $notifications) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('student') && !$user->isAdmin()) {
            return $this->studentIndex($request, $user);
        }

        return $this->staffIndex($request, $user);
    }

    public function storeGrade(Request $request, Classroom $classroom): RedirectResponse
    {
        $user = $request->user();
        $this->abortUnlessCanManageCatalog($user, $classroom);

        $validated = $request->validate([
            'student_user_id' => [
                'required',
                'integer',
                Rule::exists('classroom_members', 'user_id')
                    ->where(fn ($query) => $query
                        ->where('classroom_id', $classroom->id)
                        ->where('role', 'student')),
            ],
            'grade_value' => ['required', 'numeric', 'min:1', 'max:10'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'send_warning_mail' => ['nullable', 'boolean'],
        ]);

        $student = User::query()->findOrFail((int) $validated['student_user_id']);
        if (!$student->hasRole('student')) {
            return back()->withErrors(['student_user_id' => 'Utilizatorul selectat nu are rol de student.']);
        }

        $grade = ClassroomGrade::query()->firstOrNew([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
        ]);

        $previousGrade = $grade->exists ? (float) $grade->grade_value : null;
        $grade->grade_value = round((float) $validated['grade_value'], 2);
        $grade->feedback = $validated['feedback'] ?? null;
        $grade->graded_by_user_id = $user->id;
        if ((float) $grade->grade_value >= 5.0) {
            $grade->last_warning_sent_at = null;
        }
        $grade->save();

        AuditLogger::log('catalog.grade.save', $user, 'classroom_grade', $grade->id, [
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'grade_value' => (float) $grade->grade_value,
            'is_update' => $previousGrade !== null,
        ]);

        $isFailing = (float) $grade->grade_value < 5.0;
        $becameFailing = $previousGrade === null || $previousGrade >= 5.0;
        $sendWarningExplicitly = $request->boolean('send_warning_mail');

        $sentWarning = false;
        if ($isFailing && ($becameFailing || $sendWarningExplicitly)) {
            $sentWarning = $this->notifications->sendFailingWarning($grade, $user, $sendWarningExplicitly);
        }

        $gradeMailSent = $this->notifications->sendGradeAssigned($grade, $user, $previousGrade !== null);

        $successMessage = $previousGrade === null
            ? 'Nota a fost inregistrata in catalog.'
            : 'Nota a fost actualizata in catalog.';
        if ($gradeMailSent) {
            $successMessage .= ' Studentul a primit email cu nota.';
        }
        if ($sentWarning) {
            $successMessage .= ' Studentul a fost notificat pe email pentru restanta.';
        }

        return redirect()
            ->route('catalog.index', ['classroom_id' => $classroom->id])
            ->with('success', $successMessage);
    }

    public function sendRetakeDetails(Request $request, Classroom $classroom): RedirectResponse
    {
        $user = $request->user();
        $this->abortUnlessCanManageCatalog($user, $classroom);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'integer',
                Rule::exists('classroom_members', 'user_id')
                    ->where(fn ($query) => $query
                        ->where('classroom_id', $classroom->id)
                        ->where('role', 'student')),
            ],
            'details' => ['required', 'string', 'max:4000'],
        ]);

        $studentIds = array_values(array_unique(array_map('intval', $validated['student_ids'])));
        $failingGrades = ClassroomGrade::query()
            ->with(['student', 'classroom.createdBy', 'gradedBy'])
            ->where('classroom_id', $classroom->id)
            ->whereIn('student_user_id', $studentIds)
            ->where('grade_value', '<', 5)
            ->get()
            ->keyBy('student_user_id');

        if ($failingGrades->isEmpty()) {
            return back()->withErrors([
                'student_ids' => 'Nu exista studenti restanti in selectia trimisa.',
            ]);
        }

        $sentCount = 0;
        foreach ($studentIds as $studentId) {
            $grade = $failingGrades->get($studentId);
            if (!$grade) {
                continue;
            }

            if ($this->notifications->sendRetakeDetails($grade, $user, $validated['details'])) {
                $sentCount++;
            }
        }

        if ($sentCount === 0) {
            return back()->withErrors([
                'student_ids' => 'Nu am putut trimite emailurile de restanta. Verifica setarile de mail.',
            ]);
        }

        AuditLogger::log('catalog.retake_mail.bulk', $user, 'classroom', $classroom->id, [
            'target_count' => count($studentIds),
            'sent_count' => $sentCount,
        ]);

        $message = $sentCount === 1
            ? 'Emailul cu detalii pentru restanta a fost trimis.'
            : 'Au fost trimise ' . $sentCount . ' emailuri cu detalii pentru restanta.';

        return redirect()
            ->route('catalog.index', ['classroom_id' => $classroom->id])
            ->with('success', $message);
    }

    private function staffIndex(Request $request, User $user): View
    {
        $subjectFilter = trim((string) $request->query('subject', ''));
        $studyYearFilter = (int) $request->query('study_year', 0);
        $studentSearch = trim((string) $request->query('student_search', ''));
        $statusFilter = (string) $request->query('status', 'all');

        $availableStudyYearsQuery = Classroom::query()->whereNotNull('study_year');
        if (!$user->isAdmin()) {
            $availableStudyYearsQuery->where('created_by', $user->id);
        }
        $availableStudyYears = $availableStudyYearsQuery
            ->orderBy('study_year')
            ->distinct()
            ->pluck('study_year');

        $classroomsQuery = Classroom::query()
            ->with('createdBy')
            ->orderBy('subject')
            ->orderBy('name');

        if (!$user->isAdmin()) {
            $classroomsQuery->where('created_by', $user->id);
        }
        if ($subjectFilter !== '') {
            $classroomsQuery->where('subject', 'like', '%' . $subjectFilter . '%');
        }
        if ($studyYearFilter > 0) {
            $classroomsQuery->where('study_year', $studyYearFilter);
        }

        $classrooms = $classroomsQuery->get();

        $selectedClassroom = null;
        $students = collect();
        $failingStudents = collect();

        if ($classrooms->isNotEmpty()) {
            $requestedId = (int) $request->query('classroom_id', (int) $classrooms->first()->id);
            $selectedClassroom = $classrooms->firstWhere('id', $requestedId) ?? $classrooms->first();

            if ($selectedClassroom) {
                $students = $this->buildStudentRowsForClassroom($selectedClassroom);
                if ($studentSearch !== '') {
                    $students = $students->filter(function (array $row) use ($studentSearch): bool {
                        $needle = Str::lower($studentSearch);
                        $name = Str::lower((string) ($row['student']->name ?? ''));
                        $email = Str::lower((string) ($row['student']->email ?? ''));

                        return Str::contains($name, $needle) || Str::contains($email, $needle);
                    })->values();
                }

                $students = $this->filterRowsByStatus($students, $statusFilter);
                $failingStudents = $students->where('is_failing', true)->values();
            }
        }

        return view('catalog.index', [
            'title' => 'Catalog',
            'mode' => 'staff',
            'classrooms' => $classrooms,
            'selected_classroom' => $selectedClassroom,
            'student_rows' => $students,
            'failing_students' => $failingStudents,
            'filters' => [
                'subject' => $subjectFilter,
                'study_year' => $studyYearFilter,
                'student_search' => $studentSearch,
                'status' => $statusFilter,
            ],
            'available_study_years' => $availableStudyYears,
        ]);
    }

    private function studentIndex(Request $request, User $student): View
    {
        $search = trim((string) $request->query('search', ''));
        $statusFilter = (string) $request->query('status', 'all');
        $studyYearFilter = (int) $request->query('study_year', 0);

        $classrooms = Classroom::query()
            ->with('createdBy')
            ->whereHas('members', fn ($query) => $query->where('users.id', $student->id))
            ->orderBy('subject')
            ->orderBy('name')
            ->get();
        if ($studyYearFilter > 0) {
            $classrooms = $classrooms
                ->where('study_year', $studyYearFilter)
                ->values();
        }

        $grades = ClassroomGrade::query()
            ->with(['gradedBy', 'classroom.createdBy'])
            ->where('student_user_id', $student->id)
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->get()
            ->keyBy('classroom_id');

        $records = $classrooms->map(function (Classroom $classroom) use ($grades): array {
            $grade = $grades->get($classroom->id);

            return [
                'classroom_id' => $classroom->id,
                'classroom_name' => $classroom->name,
                'subject' => $classroom->subject,
                'study_year' => $classroom->study_year,
                'professor_name' => $classroom->createdBy?->name ?? '-',
                'grade_value' => $grade ? (float) $grade->grade_value : null,
                'feedback' => $grade?->feedback,
                'graded_at' => $grade?->updated_at,
                'graded_by' => $grade?->gradedBy?->name,
                'is_failing' => $grade ? $grade->isFailing() : false,
                'details_url' => route('classrooms.show', $classroom),
            ];
        })->values();
        if ($search !== '') {
            $records = $records->filter(function (array $record) use ($search): bool {
                $needle = Str::lower($search);

                return Str::contains(Str::lower((string) $record['subject']), $needle)
                    || Str::contains(Str::lower((string) $record['classroom_name']), $needle)
                    || Str::contains(Str::lower((string) $record['professor_name']), $needle);
            })->values();
        }
        $records = $this->filterRowsByStatus($records, $statusFilter);

        $availableStudyYears = Classroom::query()
            ->whereHas('members', fn ($query) => $query->where('users.id', $student->id))
            ->whereNotNull('study_year')
            ->orderBy('study_year')
            ->distinct()
            ->pluck('study_year');

        return view('catalog.index', [
            'title' => 'Situatie scolara',
            'mode' => 'student',
            'records' => $records,
            'failing_count' => $records->where('is_failing', true)->count(),
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'study_year' => $studyYearFilter,
            ],
            'available_study_years' => $availableStudyYears,
        ]);
    }

    private function buildStudentRowsForClassroom(Classroom $classroom): Collection
    {
        $students = $classroom->members()
            ->wherePivot('role', 'student')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.avatar_url']);

        $grades = ClassroomGrade::query()
            ->with('gradedBy')
            ->where('classroom_id', $classroom->id)
            ->whereIn('student_user_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_user_id');

        return $students->map(function (User $student) use ($grades): array {
            $grade = $grades->get($student->id);

            return [
                'student' => $student,
                'grade_id' => $grade?->id,
                'grade_value' => $grade ? (float) $grade->grade_value : null,
                'feedback' => $grade?->feedback,
                'graded_by' => $grade?->gradedBy?->name,
                'graded_at' => $grade?->updated_at,
                'is_failing' => $grade ? $grade->isFailing() : false,
            ];
        })->values();
    }

    private function abortUnlessCanManageCatalog(?User $user, Classroom $classroom): void
    {
        $isStaff = $user && ($user->hasRole('profesor') || $user->isAdmin());

        abort_unless($isStaff && ClassroomAccess::canManageClassroom($user, $classroom), 403);
    }

    private function filterRowsByStatus(Collection $rows, string $statusFilter): Collection
    {
        return match ($statusFilter) {
            'failing' => $rows->where('is_failing', true)->values(),
            'passed' => $rows->filter(fn (array $row): bool => $row['grade_value'] !== null && !$row['is_failing'])->values(),
            'ungraded' => $rows->where('grade_value', null)->values(),
            default => $rows,
        };
    }
}
