<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamSolutionRequest;
use App\Models\Exam;
use App\Models\ExamSolution;
use App\Models\Folder;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamSolutionController extends Controller
{
    public function index()
    {
        $text = isset(request()->text) ? request()->text : '';
        if (request()->page_size) {
            return User::where("name", "like", "%$text%")->orderBy("id", "desc")
                ->paginate(request()->page_size);
        }
        return User::get();
    }
    public function getFolders()
    {
        $text = isset(request()->text) ? request()->text : '';
        if (request()->page_size) {
            return Folder::where("name", "like", "%$text%")->with("parent")->orderBy("id", "desc")
                ->when(request()->parent_id, function ($q) {
                    $q->where("parent_id", request()->parent_id);
                }, function ($q) {
                    $q->whereNull("parent_id");
                })
                ->where("user_id", request()->user_id)
                ->paginate(request()->page_size);
        }
        return Folder::get();
    }

    public function getExams()
    {
        $currentUserId = JWTAuth::parseToken([])->getPayload()->get("sub");
        $text = isset(request()->text) ? request()->text : '';
        $folderId = request()->folder_id;
        if (request()->page_size) {
            return Exam::with(["examSolutions" => function ($query) use ($currentUserId) {
                $query->where("user_id", $currentUserId);
            }])->where("title", "like", "%$text%")->orderBy("id", "desc")
                ->when($folderId, function ($q) use ($folderId) {
                    $q->where("folder_id", $folderId);
                }, function ($q) {
                    $q->whereNull("folder_id");
                })
                ->where("user_id", request()->user_id)
                ->paginate(request()->page_size);
        }
        return Exam::get();
    }
    function solve(ExamSolutionRequest $request)
    {
        $currentUserId = JWTAuth::parseToken([])->getPayload()->get("sub");
        $exam = Exam::with(["examSolutions" => function ($query) use ($currentUserId) {
            $query->where("user_id", $currentUserId);
        }])->find($request->exam_id);
        if ($this->examUnavailable($exam)) return response()->json(["error" => "Exam unavailable"], 400);
        $solutionsStates = $this->getSolutionsStates($exam->questions, $request->solutions);
        ExamSolution::create([
            "exam_id" => $request->exam_id,
            "user_id" => JWTAuth::parseToken()->getPayload()->get("sub"),
            "result" => $solutionsStates["result"],
            "solutions" => $solutionsStates["solutions"]
        ]);
        return $solutionsStates;
    }
    //Commons
    private function getSolutionsStates($examQuestions, $requestSolutions)
    {
        $solutions = [];
        $correctAnswerCounter = 0;
        foreach ($requestSolutions as $solutionIndex => $solution) {
            $correctSelectionIndex = 0;
            $correctAnswer = $examQuestions[$solution["questionIndex"]]["selections"][$solution["selectedSelectionIndex"]]["selected"];
            if ($correctAnswer) {
                $correctAnswerCounter++;
                $correctSelectionIndex = $solution["selectedSelectionIndex"];
            } else {
                $correctSelectionIndex = $this->getCorrectSelection($examQuestions, $solution["questionIndex"]);
            }
            $solutions[] = [
                "questionIndex" => $solution["questionIndex"],
                "selectedSelectionIndex" => $solution["selectedSelectionIndex"],
                "correctSelectionIndex" => $correctSelectionIndex
            ];
        }
        return [
            "result" => $correctAnswerCounter . "/" . count($examQuestions),
            "solutions" => $solutions
        ];
    }
    private function getCorrectSelection($examQuestions, $questionIndex)
    {
        foreach ($examQuestions[$questionIndex]["selections"]
            as $selectionIndex => $selection) {
            if ($selection["selected"]) return $selectionIndex;
        }
    }
    private function examUnavailable($exam)
    {
        return $exam->examSolutions || (
            ($exam->start_date ? Carbon::now()->addHours(2)->lt($exam->start_date) : false)
            ||
            ($exam->end_date ? Carbon::now()->addHours(2)->gt($exam->end_date) : false)
        );
    }
}
