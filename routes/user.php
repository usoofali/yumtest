<?php
/**
 * File name: user.php
 * Last modified: 20/07/21, 2:27 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PracticeController;
use App\Http\Controllers\User\QuizController;
use App\Http\Controllers\User\ProgressController;
use App\Http\Controllers\User\QuizScheduleController;
use App\Http\Controllers\User\SyllabusController;
use App\Http\Controllers\User\PracticeLessonController;
use App\Http\Controllers\User\PracticeVideoController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\SubscriptionController;
use App\Http\Controllers\User\PaymentController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Syllabus Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/change-syllabus', [SyllabusController::class, 'changeSyllabus'])->name('change_syllabus');
    Route::post('/update-syllabus', [SyllabusController::class, 'updateSyllabus'])->name('update_syllabus');

    /*
    |--------------------------------------------------------------------------
    | Common Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user_dashboard');
    Route::get('/learn-practice', [DashboardController::class, 'learn'])->name('learn_practice');
    Route::get('/learn-practice/{category:slug}/{section}', [DashboardController::class, 'learnSection'])->name('learn_practice_section');

    Route::get('/quizzes', [DashboardController::class, 'quiz'])->name('quiz_dashboard');
    Route::get('/quizzes/{type:slug}', [DashboardController::class, 'quizzesByType'])->name('quizzes_by_type');
    Route::get('/fetch-quizzes-by/{type:slug}', [DashboardController::class, 'fetchQuizzesByType'])->name('fetch_quizzes_by_type');
    Route::get('/live-quizzes', [DashboardController::class, 'liveQuizzes'])->name('live_quizzes');
    Route::get('/fetch-live-quizzes', [DashboardController::class, 'fetchLiveQuizzes'])->name('fetch_live_quizzes');

    /*
    |--------------------------------------------------------------------------
    | Payment & Checkout Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/checkout/{plan}', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/{plan}', [CheckoutController::class, 'processCheckout'])->name('process_checkout');
    Route::get('/payment-cancelled', [CheckoutController::class, 'paymentCancelled'])->name('payment_cancelled');
    Route::get('/payment-pending', [CheckoutController::class, 'paymentPending'])->name('payment_pending');
    Route::get('/payment-success', [CheckoutController::class, 'paymentSuccess'])->name('payment_success');
    Route::get('/payment-failed', [CheckoutController::class, 'paymentFailed'])->name('payment_failed');

    Route::post('/callbacks/razorpay', [CheckoutController::class, 'handleRazorpayPayment'])->name('razorpay_callback');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('user_subscriptions');
    Route::post('/cancel-subscription/{id}', [SubscriptionController::class, 'cancelSubscription'])->name('cancel_my_subscription');
    Route::get('/payments', [PaymentController::class, 'index'])->name('user_payments');
    Route::get('/download-invoice/{id}', [PaymentController::class, 'downloadInvoice'])->name('download_invoice');

    /*
    |--------------------------------------------------------------------------
    | Practice Video Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/videos/{category:slug}/{section:slug}/{skill}', [PracticeVideoController::class, 'skillVideos'])->name('skill_videos');
    Route::get('/fetch-practice-videos/{category:slug}/{section:slug}/{skill}', [PracticeVideoController::class, 'fetchPracticeVideos'])->name('fetch_practice_videos');
    Route::get('/watch/{category:slug}/{section:slug}/{skill}/watch', [PracticeVideoController::class, 'watchVideos'])->name('watch_videos');

    /*
    |--------------------------------------------------------------------------
    | Practice Lesson Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/lessons/{category:slug}/{section:slug}/{skill}', [PracticeLessonController::class, 'skillLessons'])->name('skill_lessons');
    Route::get('/fetch-practice-lessons/{category:slug}/{section:slug}/{skill}', [PracticeLessonController::class, 'fetchPracticeLessons'])->name('fetch_practice_lessons');
    Route::get('/lessons/{category:slug}/{section:slug}/{skill}/read', [PracticeLessonController::class, 'readLessons'])->name('read_lessons');

    /*
    |--------------------------------------------------------------------------
    | Practice Set Routes
    |--------------------------------------------------------------------------
    */
    Route::get('practice/{category:slug}/{section:slug}/{skill}/practice-sets', [PracticeController::class, 'practiceSets'])->name('skill_practice_sets');
    Route::get('/fetch-practice-sets/{category:slug}/{section:slug}/{skill}', [PracticeController::class, 'fetchPracticeSets'])->name('fetch_practice_sets');

    Route::get('/practice/{practiceSet:slug}/init', [PracticeController::class, 'initPracticeSet'])->name('init_practice_set');
    Route::get('/practice/{practiceSet:slug}/{session}', [PracticeController::class, 'goToPractice'])->name('go_to_practice');
    Route::get('/practice/{practiceSet:slug}/questions/{session}', [PracticeController::class, 'getPracticeQuestions'])->name('fetch_practice_questions');
    Route::post('/check_practice_answer/{practiceSet:slug}/{session}', [PracticeController::class, 'checkAnswer'])->name('check_practice_answer');
    Route::get('/practice/{practiceSet:slug}/analysis/{session}', [PracticeController::class, 'analysis'])->name('get_practice_session_analysis');
    Route::post('/practice/{practiceSet:slug}/analysis/{session}', [PracticeController::class, 'analysis'])->name('practice_session_analysis');
    Route::post('/practice/{practiceSet:slug}/finish/{session}', [PracticeController::class, 'finish'])->name('finish_practice_session');
    Route::get('/practice/{practiceSet:slug}/solutions/{session}', [PracticeController::class, 'solutions'])->name('fetch_practice_set_solutions');

    /*
    |--------------------------------------------------------------------------
    | User Quiz Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz:slug}/instructions', [QuizController::class, 'instructions'])->name('quiz_instructions');
    Route::get('/quiz/{quiz:slug}/init', [QuizController::class, 'initQuiz'])->name('init_quiz');

    Route::get('/quiz/{quiz:slug}/{schedule}/instructions', [QuizScheduleController::class, 'instructions'])->name('quiz_schedule_instructions');
    Route::get('/quiz/{quiz:slug}/{schedule}/init', [QuizScheduleController::class, 'initQuizSchedule'])->name('init_quiz_schedule');

    Route::get('/quiz/{quiz:slug}/{session}', [QuizController::class, 'goToQuiz'])->name('go_to_quiz');
    Route::get('/quiz/{quiz:slug}/questions/{session}', [QuizController::class, 'getQuizQuestions'])->name('fetch_quiz_questions');
    Route::post('/update_quiz_answer/{quiz:slug}/{session}', [QuizController::class, 'updateAnswer'])->name('update_quiz_answer');
    Route::post('/quiz/{quiz:slug}/finish/{session}', [QuizController::class, 'finish'])->name('finish_quiz_session');
    Route::get('/quiz/{quiz:slug}/thank-you/{session}', [QuizController::class, 'thankYou'])->name('quiz_thank_you');
    Route::get('/quiz/{quiz:slug}/results/{session}', [QuizController::class, 'results'])->name('quiz_results');
    Route::get('/quiz/{quiz:slug}/solutions/{session}', [QuizController::class, 'solutions'])->name('fetch_quiz_solutions');
    Route::get('/quiz/{quiz:slug}/download-report/{session}', [QuizController::class, 'exportPDF'])->name('download_quiz_report');

    /*
    |--------------------------------------------------------------------------
    | User Progress Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/my-progress', [ProgressController::class, 'myProgress'])->name('my_progress');
    Route::get('/my-practice', [ProgressController::class, 'myPractice'])->name('my_practice');
    Route::get('/my-quizzes', [ProgressController::class, 'myQuizzes'])->name('my_quizzes');
});
