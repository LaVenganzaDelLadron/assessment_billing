<?php


use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Roles\RoleController;
use App\Http\Controllers\Roles\UserRoleController;
use App\Http\Controllers\Academics\SchoolController;
use App\Http\Controllers\Academics\ClassesController;
use App\Http\Controllers\Academics\EnrollmentController;
use App\Http\Controllers\Academics\SubjectController;
use App\Http\Controllers\Academics\TeacherSubController;
use App\Http\Controllers\Academics\AssignmentController;
use App\Http\Controllers\Academics\SubmissionController;
use App\Http\Controllers\Academics\StudentGradeController;
use App\Http\Controllers\Billing\FeesController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\AssessmentController;
use App\Http\Controllers\Academics\YearController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register','register')->name('register');
    Route::post('/login','login')->name('login');
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('school')->controller(SchoolController::class)->group(function () {
        Route::get('/','index')->name('school.index');
        Route::post('/','store')->name('school.store');
        Route::put('/{id}','update')->name('school.update');
        Route::get('/{id}','show')->name('school.show');
        Route::delete('/{id}','destroy')->name('school.destroy');
    });

    Route::prefix('classes')->controller(ClassesController::class)->group(function () {
        Route::get('/','index')->name('class.index');
        Route::post('/','store')->name('class.store');
        Route::put('/{id}','update')->name('class.update');
        Route::get('/{id}','show')->name('class.show');
        Route::delete('/{id}','destroy')->name('class.destroy');
    });

    Route::prefix('enrollment')->controller(EnrollmentController::class)->group(function () {
        Route::get('/','index')->name('enrollment.index');
        Route::post('/','store')->name('enrollment.store');
        Route::put('/{id}','update')->name('enrollment.update');
        Route::get('/{id}','show')->name('enrollment.show');
        Route::delete('/{id}','destroy')->name('enrollment.destroy');
    });

    Route::prefix('subject')->controller(SubjectController::class)->group(function () {
        Route::get('/','index')->name('subject.index');
        Route::post('/','store')->name('subject.store');
        Route::put('/{id}','update')->name('subject.update');
        Route::get('/{id}','show')->name('subject.show');
        Route::delete('/{id}','destroy')->name('subject.destroy');
    });

    Route::prefix('teacher-subject')->controller(TeacherSubController::class)->group(function () {
        Route::get('/','index')->name('teacher-subject.index');
        Route::post('/','store')->name('teacher-subject.store');
        Route::put('/{id}','update')->name('teacher-subject.update');
        Route::get('/{id}','show')->name('teacher-subject.show');
        Route::delete('/{id}','destroy')->name('teacher-subject.destroy');
    });

    Route::prefix('assignment')->controller(AssignmentController::class)->group(function () {
        Route::get('/','index')->name('assignment.index');
        Route::post('/','store')->name('assignment.store');
        Route::put('/{id}','update')->name('assignment.update');
        Route::get('/{id}','show')->name('assignment.show');
        Route::delete('/{id}','destroy')->name('assignment.destroy');
    });

    Route::prefix('grade')->controller(StudentGradeController::class)->group(function () {
        Route::get('/','index')->name('grade.index');
        Route::post('/','store')->name('grade.store');
        Route::put('/{id}','update')->name('grade.update');
        Route::get('/{id}','show')->name('grade.show');
        Route::delete('/{id}','destroy')->name('grade.destroy');
    });

    Route::prefix('fees')->controller(FeesController::class)->group(function () {
        Route::get('/','index')->name('fees.index');
        Route::post('/','store')->name('fees.store');
        Route::put('/{id}','update')->name('fees.update');
        Route::get('/{id}','show')->name('fees.show');
        Route::delete('/{id}','destroy')->name('fees.destroy');
    });

    Route::prefix('billing')->controller(BillingController::class)->group(function () {
        Route::get('/','index')->name('billing.index');
        Route::post('/','store')->name('billing.store');
        Route::put('/{id}','update')->name('billing.update');
        Route::get('/{id}','show')->name('billing.show');
        Route::delete('/{id}','destroy')->name('billing.destroy');
    });

    Route::prefix('assessment')->controller(AssessmentController::class)->group(function () {
        Route::get('/', 'index')->name('assessment.index');
        Route::post('/{studentId}', 'store')->name('assessment.store');
        Route::get('/{studentId}', 'show')->name('assessment.show');
        Route::post('/{studentId}/apply-scholarship', 'applyScholarship')->name('assessment.applyScholarship');
        Route::get('/{studentId}/breakdown', 'breakdown')->name('assessment.breakdown');
    });

    Route::prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::get('/','index')->name('payment.index');
        Route::post('/','store')->name('payment.store');
        Route::put('/{id}','update')->name('payment.update');
        Route::get('/{id}','show')->name('payment.show');
        Route::delete('/{id}','destroy')->name('payment.destroy');
    });


    Route::prefix('submission')->controller(SubmissionController::class)->group(function () {
        Route::get('/','index')->name('submission.index');
        Route::post('/','store')->name('submission.store');
        Route::put('/{id}','update')->name('submission.update');
        Route::get('/{id}','show')->name('submission.show');
        Route::delete('/{id}','destroy')->name('submission.destroy');
    });

});

    Route::prefix('year')->controller(YearController::class)->group(function () {
        Route::get('/','index')->name('year.index');
        Route::post('/','store')->name('year.store');
        Route::put('/{id}','update')->name('year.update');
        Route::get('/{id}','show')->name('year.show');
        Route::delete('/{id}','destroy')->name('year.destroy');
    });

    Route::prefix('role')->controller(RoleController::class)->group(function () {
        Route::get('/','index')->name('role.index');
        Route::post('/','store')->name('role.store');
        Route::put('/{id}','update')->name('role.update');
        Route::get('/{id}','show')->name('role.show');
        Route::delete('/{id}','destroy')->name('role.destroy');
    });
    Route::prefix('user-role')->controller(UserRoleController::class)->group(function () {
        Route::get('/','index')->name('user-role.index');
        Route::post('/','store')->name('user-role.store');
        Route::put('/{id}','update')->name('user-role.update');
        Route::get('/{id}','show')->name('user-role.show');
        Route::delete('/{id}','destroy')->name('user-role.destroy');
    });
