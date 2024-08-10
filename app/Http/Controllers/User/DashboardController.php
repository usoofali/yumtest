<?php
/**
 * File name: DashboardController.php
 * Last modified: 18/07/21, 3:59 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PracticeSession;
use App\Models\QuizSchedule;
use App\Models\QuizType;
use App\Models\Section;
use App\Models\SubCategory;
use App\Settings\LocalizationSettings;
use App\Transformers\Platform\PracticeSessionCardTransformer;
use App\Transformers\Platform\QuizCardTransformer;
use App\Transformers\Platform\QuizScheduleCalendarTransformer;
use App\Transformers\Platform\QuizScheduleCardTransformer;
use App\Transformers\Platform\QuizTypeTransformer;
use App\Transformers\Platform\SubCategoryCardTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private LocalizationSettings $localizationSettings;

    public function __construct(LocalizationSettings $localizationSettings)
    {
        $this->middleware(['role:guest|student|employee', 'verify.syllabus']);
        $this->localizationSettings = $localizationSettings;
    }

    /**
     * User's Main Dashboard
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $userGroups = auth()->user()->userGroups()->pluck('id');
        $minDate = Carbon::today()->timezone($this->localizationSettings->default_timezone);
        $maxDate = Carbon::today()->addMonths(1)->endOfMonth()->timezone($this->localizationSettings->default_timezone);
        $subCategory = SubCategory::find(Cookie::get('category_id'));

        // Fetch quizzes scheduled for current user via user groups
        $schedules = QuizSchedule::whereHas('userGroups', function (Builder $query) use ($userGroups) {
            $query->whereIn('user_group_id', $userGroups);
        })->whereHas('quiz', function (Builder $query) use ($subCategory) {
            $query->where('sub_category_id', $subCategory->id);
        })->with('quiz')->orderBy('end_date', 'asc')->active()->limit(4)->get();

        // Fetch current user in-completed practice sessions
        $practiceSessions = PracticeSession::with(['practiceSet' => function($query) {
            $query->with('skill:id,name');
        }])->whereHas('practiceSet', function (Builder $query) use ($subCategory) {
            $query->where('sub_category_id', $subCategory->id);
        })->where('user_id', auth()->user()->id)->pending()
            ->orderBy('updated_at', 'desc')->limit(1)->get();

        return Inertia::render('User/Dashboard', [
            'scheduleCalendar' => fractal($schedules, new QuizScheduleCalendarTransformer())->toArray()['data'],
            'practiceSessions' => fractal($practiceSessions, new PracticeSessionCardTransformer())->toArray()['data'],
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }

    /**
     * User's Learn & Practice Screen
     *
     * @return \Inertia\Response
     */
    public function learn()
    {
        return Inertia::render('User/LearnPractice', [
            'category' => fractal(SubCategory::with(['sections:id,name,code,slug', 'subCategoryType:id,name', 'category:id,name'])
                ->orderBy('name')->find(Cookie::get('category_id')), new SubCategoryCardTransformer())
                ->toArray()['data']
        ]);
    }

    /**
     * Section's Learn & Practice Screen
     *
     * @param SubCategory $category
     * @param $section
     * @return \Inertia\Response
     */
    public function learnSection(SubCategory $category, $section)
    {
        $catId = $category->id;
        $section = Section::with(['skills' => function($query) use ($catId) {
            $query->withCount(['practiceSets' => function (Builder $builder) use ($catId) {
                $builder->where('is_active', '=', 1)->where('sub_category_id', '=', $catId);
            }, 'practiceLessons' => function (Builder $builder) use ($catId) {
                $builder->where('sub_category_id', '=', $catId);
            }, 'practiceVideos' => function (Builder $builder) use ($catId) {
                $builder->where('sub_category_id', '=', $catId);
            }]);
        }])->where('slug', $section)
            ->firstOrFail();

        return Inertia::render('User/LearnPracticeSection', [
            'category' => $category,
            'section' => $section
        ]);
    }

    /**
     * User's Quiz Dashboard
     *
     * @return \Inertia\Response
     */
    public function quiz()
    {
        $userGroups = auth()->user()->userGroups()->pluck('id');
        $category = SubCategory::find(Cookie::get('category_id'));

        // Fetch quizzes scheduled for current user via user groups
        $schedules = QuizSchedule::whereHas('userGroups', function (Builder $query) use ($userGroups) {
            $query->whereIn('user_group_id', $userGroups);
        })->whereHas('quiz', function (Builder $query) use ($category) {
            $query->where('sub_category_id', '=', $category->id);
        })->with(['quiz' => function($builder) {
            $builder->with(['subCategory:id,name', 'quizType:id,name']);
        }])->orderBy('end_date', 'asc')->active()->limit(4)->get();

        // Fetch public quizzes by quiz type
        $quizTypes = QuizType::active()->orderBy('name')->get();

        return Inertia::render('User/QuizDashboard', [
            'quizSchedules' => fractal($schedules, new QuizScheduleCardTransformer())->toArray()['data'],
            'quizTypes' => fractal($quizTypes, new QuizTypeTransformer())->toArray()['data'],
            'subscription' => request()->user()->hasActiveSubscription($category->id, 'quizzes')
        ]);
    }

    /**
     * Live Quizzes Screen
     *
     * @return \Inertia\Response
     */
    public function liveQuizzes()
    {
        $category = SubCategory::find(Cookie::get('category_id'));
        return Inertia::render('User/LiveQuizzes', [
            'subscription' => request()->user()->hasActiveSubscription($category->id, 'quizzes')
        ]);
    }

    /**
     * Fetch live quizzes api endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchLiveQuizzes()
    {
        $userGroups = auth()->user()->userGroups()->pluck('id');
        $category = SubCategory::find(Cookie::get('category_id'));

        // Fetch quizzes scheduled for current user via user groups
        $schedules = QuizSchedule::whereHas('userGroups', function (Builder $query) use ($userGroups) {
            $query->whereIn('user_group_id', $userGroups);
        })->whereHas('quiz', function (Builder $query) use ($category) {
            $query->where('sub_category_id', '=', $category->id);
        })->with(['quiz' => function($builder) {
            $builder->with(['subCategory:id,name', 'quizType:id,name']);
        }])->orderBy('end_date', 'asc')->active()->paginate(10);

        return response()->json([
            'schedules' => fractal($schedules, new QuizScheduleCardTransformer())->toArray()
        ], 200);
    }

    /**
     * Get Quizzes by type page
     *
     * @param QuizType $type
     * @return \Inertia\Response
     */
    public function quizzesByType(QuizType $type)
    {
        $category = SubCategory::find(Cookie::get('category_id'));
        return Inertia::render('User/QuizzesByType', [
            'type' => $type,
            'subscription' => request()->user()->hasActiveSubscription($category->id, 'quizzes')
        ]);
    }

    /**
     * Fetch quizzes by type
     *
     * @param QuizType $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchQuizzesByType(QuizType $type)
    {
        $subCategory = SubCategory::find(Cookie::get('category_id'));

        // Fetch public quizzes by quiz type
        $quizzes = $type->quizzes()->has('questions')
            ->where('sub_category_id', '=', $subCategory->id)
            ->orderBy('quizzes.is_paid', 'asc')
            ->with(['subCategory:id,name', 'quizType:id,name'])
            ->isPublic()->published()
            ->paginate(10);

        return response()->json([
            'quizzes' => fractal($quizzes, new QuizCardTransformer())->toArray(),
        ], 200);
    }
}
